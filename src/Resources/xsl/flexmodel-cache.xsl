<?xml version='1.0' encoding='UTF-8'?>
<xsl:stylesheet version='1.0' xmlns:xsl='http://www.w3.org/1999/XSL/Transform' xmlns:php='http://php.net/xsl' exclude-result-prefixes='php'>
    <!--
    XSL stylesheet to parse flexmodel XML file to a PHP array file format.

    Used HTML entities:
    &#10; - Line feed
    &#39; - Apostrophe
    -->

    <!--
    Output as text.
    -->
    <xsl:output method='text' indent='yes'/>

    <!--
    Adds the base PHP array syntax.
    -->
    <xsl:template match='/flexmodel'>
        <xsl:text>&lt;?php</xsl:text>
        <xsl:call-template name='newline'/>
        <xsl:text>return array(</xsl:text>
        <xsl:apply-templates select='self::node()' mode='checksum'/>
        <xsl:apply-templates select='object'/>
        <xsl:call-template name='newline'/>
        <xsl:text>);</xsl:text>
        <xsl:call-template name='newline'/>
    </xsl:template>

    <!--
    Adds the file checksum.
    -->
    <xsl:template match='flexmodel' mode='checksum'>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'/>
        <xsl:text>&#39;__checksum&#39; => &#39;</xsl:text>
        <xsl:value-of select='$checksum'/>
        <xsl:text>&#39;,</xsl:text>
    </xsl:template>

    <!--
    Adds an object to the PHP array.
    -->
    <xsl:template match='object'>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'/>
        <xsl:text>&#39;</xsl:text>
        <xsl:value-of select='@name'/><xsl:text>&#39; => array(</xsl:text>
        <xsl:apply-templates select='@*' mode='generate'>
            <xsl:with-param name='parentKey' select='"name"'/>
        </xsl:apply-templates>
        <xsl:apply-templates select='node()' mode='generate'/>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'/>
        <xsl:text>),</xsl:text>
    </xsl:template>

    <!--
    Adds a key-value pair.
    -->
    <xsl:template match='node() | @*' mode='generate' name='keyValue'>
        <xsl:param name='parentKey'/>
        <xsl:param name='key' select='local-name()'/>
        <xsl:param name='value' select='.'/>
        <xsl:param name='valueContent'/>

        <xsl:if test='not($parentKey) or $parentKey != $key'>
            <xsl:call-template name='newline'/>
            <xsl:call-template name='indent'>
                <xsl:with-param name='count' select='count(ancestor::*)'/>
            </xsl:call-template>
            <xsl:text>&#39;</xsl:text>
            <xsl:value-of select='$key'/>
            <xsl:text>&#39; => </xsl:text>
            <xsl:apply-templates select='$value' mode='value'>
                <xsl:with-param name='valueContent' select='$valueContent'/>
            </xsl:apply-templates>
            <xsl:text>,</xsl:text>
        </xsl:if>
    </xsl:template>

    <!--
    Adds a value.
    -->
    <xsl:template match='node() | @*' mode='value'>
        <xsl:param name='valueContent'/>

        <xsl:choose>
            <xsl:when test='$valueContent != ""'>
                <xsl:value-of select='$valueContent'/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select='.'/>
            </xsl:otherwise>
        </xsl:choose>
    </xsl:template>

    <!--
    Adds double-quotes around the value of the node or attribute when the value should be quoted in a PHP array.
    -->
    <xsl:template match='node()[php:function("FlexModel\FlexModel::isQuotedValue", string(self::node() ) )] | @*[php:function("FlexModel\FlexModel::isQuotedValue", string(.) )]' mode='value' name='quotedValue'>
        <xsl:param name='value' select='.'/>
        <xsl:param name='valueContent'/>

        <xsl:text>&#39;</xsl:text>
        <xsl:choose>
            <xsl:when test='$valueContent'>
                <xsl:value-of select='$valueContent'/>
            </xsl:when>
            <xsl:otherwise>
                <xsl:value-of select='$value'/>
            </xsl:otherwise>
        </xsl:choose>
        <xsl:text>&#39;</xsl:text>
    </xsl:template>

    <!--
    Adds the value of @datatype without "OBJECT.".
    -->
    <xsl:template match='@datatype[substring(., 1, 7) = "OBJECT."]' mode='value'>
        <xsl:call-template name='quotedValue'>
            <xsl:with-param name='value' select='substring(., 8)'/>
        </xsl:call-template>
    </xsl:template>

    <!--
    Matches when node has attributes or childnodes.
    -->
    <xsl:template match='node()[count(self::node()/@*) &gt; 0 or count(node() ) &gt; 0 and not(self::node()/text() )]' mode='generate' name='keyArray'>
        <xsl:param name='key' select='local-name()'/>
        <xsl:param name='parentKey' select='$key'/>
        <xsl:param name='generateAttributes' select='true()'/>

        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>

        <xsl:text>&#39;</xsl:text>
        <xsl:value-of select='$key'/>
        <xsl:text>&#39; => array(</xsl:text>
        <xsl:if test='$generateAttributes = true()'>
            <xsl:apply-templates select='@*' mode='generate'>
                <xsl:with-param name='parentKey' select='$parentKey'/>
            </xsl:apply-templates>
        </xsl:if>
        <xsl:apply-templates select='node()' mode='generate'/>

        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>
        <xsl:text>),</xsl:text>
    </xsl:template>

    <!--
    Matches when parent::node() has more nodes with the same nodename.
    -->
    <xsl:template match='node()[count(parent::node()/node()[local-name() = name(current() ) ] ) &gt; 1 and (count(self::node()/@*) &gt; 0 or count(node() ) &gt; 0)]' mode='generate' name='indexArray'>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>

        <xsl:text>array(</xsl:text>
        <xsl:apply-templates select='@*' mode='generate'/>
        <xsl:apply-templates select='node()' mode='generate'/>

        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>
        <xsl:text>),</xsl:text>
    </xsl:template>

    <!--
    Matches for example classname when parent is classnames and classname only has text content.
    -->
    <xsl:template match='node()[name(parent::node() ) = concat(local-name(), "s") and count(self::node()/@*) = 0 and self::node()/text()]' mode='generate' name='indexValue'>
        <xsl:param name='value' select='self::node()'/>

        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>
        <xsl:apply-templates select='$value' mode='value'/>
        <xsl:text>,</xsl:text>
    </xsl:template>

    <!--
    Matches for example title when parent is titles and title has @name and @label and no childnodes.
    -->
    <xsl:template match='node()[name(parent::node() ) = concat(local-name(), "s") and count(self::node()/@*) = 2 and count(node() ) = 0 and self::node()/@name and self::node()/@label]' mode='generate'>
        <xsl:call-template name='keyValue'>
            <xsl:with-param name='key' select='@name'/>
            <xsl:with-param name='value' select='@label'/>
        </xsl:call-template>
    </xsl:template>

    <!--
    Always add as key array.
    -->
    <xsl:template match='object/views/view | object/forms/form | validators/validator' mode='generate'>
        <xsl:call-template name='keyArray'>
            <xsl:with-param name='key' select='@name'/>
            <xsl:with-param name='parentKey' select='"name"'/>
        </xsl:call-template>
    </xsl:template>

    <!--
    Adds a field index to the model fields.
    -->
    <xsl:template match='object/fields' mode='generate'>
        <xsl:call-template name='keyArray'/>
        <xsl:apply-templates select='self::node()' mode='generateFieldIndex'/>
    </xsl:template>

    <!--
    Always add fields as index array.
    -->
    <xsl:template match='fields/field' mode='generate'>
        <xsl:call-template name='indexArray'/>
    </xsl:template>

    <!--
    Don't process comments and text in the XML.
    -->
    <xsl:template match='comment() | text()' mode='generate'/>

    <!--
    Don't add the base xInclude attribute.
    -->
    <xsl:template match='@base' mode='generate'/>

    <!--
    Adds a field index.
    -->
    <xsl:template match='object/fields' mode='generateFieldIndex'>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>
        <xsl:text>&#39;__field_index&#39; => array(</xsl:text>
        <xsl:apply-templates select='field' mode='generateFieldIndex'/>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>
        <xsl:text>),</xsl:text>
    </xsl:template>

    <!--
    Adds a field to the field index.
    -->
    <xsl:template match='object/fields/field' mode='generateFieldIndex'>
        <xsl:call-template name='newline'/>
        <xsl:call-template name='indent'>
            <xsl:with-param name='count' select='count(ancestor::*)'/>
        </xsl:call-template>
        <xsl:text>&#39;</xsl:text>
        <xsl:value-of select='@name'/>
        <xsl:text>&#39; => </xsl:text>
        <xsl:value-of select='position() - 1'/>
        <xsl:text>,</xsl:text>
    </xsl:template>

    <!--
    Adds a line feed character.
    -->
    <xsl:template name='newline'>
        <xsl:text>&#10;</xsl:text>
    </xsl:template>

    <!--
    Adds a horizontal tab character
    -->
    <xsl:template name='indent'>
        <xsl:param name='count' select='1'/>

        <xsl:if test='$count &gt; 0'>
            <xsl:text>    </xsl:text>
            <xsl:call-template name='indent'>
                <xsl:with-param name='count' select='$count - 1'/>
            </xsl:call-template>
        </xsl:if>
    </xsl:template>

    <!--
    Skip the optiongroup node. The optiongroup label will be added as optiongroup attribute of the options under the optiongroup.
    -->
    <xsl:template match='options/optiongroup' mode='generate'>
        <xsl:apply-templates select='node()' mode='generate'/>
    </xsl:template>

    <!--
    Add the optiongroup after the last attribute of an option.
    -->
    <xsl:template match='options/optiongroup/option/@*[position() = last()]' mode='generate'>
        <xsl:call-template name='keyValue'>
            <xsl:with-param name='parentKey' select='name(parent::node())'/>
            <xsl:with-param name='key' select='local-name()'/>
            <xsl:with-param name='value' select='self::node()'/>
        </xsl:call-template>
        <xsl:call-template name='keyValue'>
            <xsl:with-param name='parentKey' select='name(parent::node())'/>
            <xsl:with-param name='key' select='"optgroup"'/>
            <xsl:with-param name='value' select='parent::node()/parent::node()/@label'/>
        </xsl:call-template>
    </xsl:template>

</xsl:stylesheet>

<?php

class ArrayToXML
{

    /**
     * Converts an given XML String into an Array. This method is statically called from {@link createArrayfromXML} createArrayfromXML
     * 
     * @param string $root  The xml string.     
     * @ignore
     */
    
    private static function xml2Array( $root )
    {
        
        $result = array();
        
        if ( $root->hasAttributes() )
        {
            $attrs = $root->attributes;
            
            foreach ( $attrs as $i => $attr )
                
                $result[$attr->name] = $attr->value;
        }
        
        $children = $root->childNodes;
        
        if ( isset( $children->length ) and $children->length == 1 )
        {
            
            $child = $children->item( 0 );
            
            if ( $child->nodeType == XML_TEXT_NODE )
            {
                $result['_value'] = $child->nodeValue;
                
                if ( count( $result ) == 1 )
                    return $result['_value'];
                
                else
                    return $result;
            }
        }
        
        $group = array();
        
        for ( $i = 0; $i < $children->length; $i ++ )
        {
            $child = $children->item( $i );
            
            if ( ! isset( $result[$child->nodeName] ) )
                $result[$child->nodeName] = self::xml2Array( $child );
            else
            {
                if ( ! isset( $group[$child->nodeName] ) )
                {
                    $tmp = $result[$child->nodeName];
                    
                    $result[$child->nodeName] = array( 
                        $tmp 
                    );
                    $group[$child->nodeName] = 1;
                }
                
                $result[$child->nodeName][] = self::xml2Array( $child );
            }
        }
        
        return $result;
    }

    /**
     * 
     * This method is statically called from _request {@link _request} when $resultSet is true.
     * 
     * @param string $xmlDoc  The XML based on $resultSet.     
     * @ignore
     */
    private static function createArrayfromXML( $xmlDoc )
    {
        $xml = new DOMDocument();
        $xml->preserveWhiteSpace = false;
        $xml->loadXML( $xmlDoc );
        return self::xml2Array( $xml->documentElement );
    
    }

    /**
     * The main function for converting to an XML document.
     * Pass in a multi dimensional array and this recrusively loops through and builds up an XML document.
     *
     * @param array $data
     * @param string $rootNodeName - what you want the root node to be - defaultsto data.
     * @param SimpleXMLElement $xml - should only be used recursively
     * @return string XML
     */
    public static function toXML( $data, &$node = null )
    {
        if ( $node === null )
        {
            $doc = new DOMDocument( '1.0', 'UTF-8' );
            $doc->preserveWhiteSpace = false;
            $node = $doc->createElement( 'resultSet' );
            $doc->appendChild( $node );
        }
        
        // loop through the data passed in.
        foreach ( $data as $key => $value )
        {
            // delete any char not allowed in XML element names
            $key = preg_replace( '/[^a-z0-9\-\_\.\:]/i', '', $key );
            
            // if there is another array found recrusively call this function
            if ( is_array( $value ) )
            {
                
                if ( is_numeric( $key ) )
                {
                    $key = 'item';
                }
                $node2 = $node->ownerDocument->createElement( $key );
                self::toXml( $value, $node2 );
                $node->appendChild( $node2 );
            }
            else
            {
                if ( is_numeric( $key ) )
                {
                    $key = 'item';
                }
                $value = htmlspecialchars( $value, ENT_QUOTES, 'UTF-8' );
                $data = $node->ownerDocument->createTextNode( $value );
                $node2 = $node->ownerDocument->createElement( $key );
                $node2->appendChild( $data );
                $node->appendChild( $node2 );
            }
        }
        if ( isset( $doc ) )
        {
            return $doc->saveXML();
        }
    }

    /**
     * Convert an XML document to a multi dimensional array
     * Pass in an XML document (or SimpleXMLElement object) and this recrusively loops through and builds a representative array
     *
     * @param string $xml - XML document - can optionally be a SimpleXMLElement object
     * @return array ARRAY
     */
    public static function toArray( $xml )
    {
        if ( is_string( $xml ) and ( empty( $xml ) or ( strpos( $xml, '<?xml' ) !== false and strlen( $xml ) == 52 ) ) )
        {
            return array();
        }
        
        if ( is_string( $xml ) )
        {
            $xml = new SimpleXMLElement( $xml );
        }
        
        $children = $xml->children();
        
        if ( ! $children )
            return (string) $xml;
        
        $arr = array();
        foreach ( $children as $key => $node )
        {
            $node = ArrayToXML::toArray( $node );
            
            // support for 'anon' non-associative arrays
            if ( $key == 'anon' )
                $key = count( $arr );
            
     // if the node is already set, put it into an array
            if ( isset( $arr[$key] ) )
            {
                if ( ! is_array( $arr[$key] ) || $arr[$key][0] == null )
                    $arr[$key] = array( 
                        $arr[$key] 
                    );
                $arr[$key][] = $node;
            }
            else
            {
                if ( $key == 'item' )
                {
                    $arr[] = $node;
                }
                else
                {
                    $arr[$key] = $node;
                }
            }
        }
        return $arr;
    }
    /**
     * Determine if a variable is an associative array
     * @return boolean
     */
    private static function isAssoc( $array )
    {
        return ( is_array( $array ) && 0 !== count( array_diff_key( $array, array_keys( array_keys( $array ) ) ) ) );
    }
}

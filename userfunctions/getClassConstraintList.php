<?php
    
    function getClassConstraintList( $class_identifier = false, $contentclass_id = false, $debug = false)
    {
        //todo debug
        
        if ( !$contentclass_id && !$class_identifier )
            return;
		
		if ( $contentclass_id && $class_identifier )
            return;
        
        $ezobjectrelationlist = eZContentClassAttribute::fetchFilteredList( array( 'data_type_string' => 'ezobjectrelationlist') );
        
        $return = array();
        
		if ( $contentclass_id )
		{
			foreach( $ezobjectrelationlist as $attribute )
			{
				if ( $attribute->attribute('contentclass_id') == $contentclass_id )
				{               				
					$attributeContent = $attribute->content();
					if ( !empty( $attributeContent['class_constraint_list'] ) )
					{
						$return = array_merge( $return, $attributeContent['class_constraint_list'] );
					}
				}
				
			}
			if ( !empty( $return ) )
				return $return;
			else
				return false;			
		}
		
		if ( $class_identifier )
		{			
			foreach( $ezobjectrelationlist as $attribute )
			{
				$attributeContent = $attribute->content();
				if ( !empty( $attributeContent['class_constraint_list'] ) )
				{					
					if ( in_array( $class_identifier, $attributeContent['class_constraint_list']  ) )
					{
						$class = eZContentClass::fetch( $attribute->attribute('contentclass_id') );
                        $classIdentifier = eZContentClass::classIdentifierByID( $attribute->attribute('contentclass_id') );
                        $return[$classIdentifier] = array(
                            'class_id' => $attribute->attribute('contentclass_id'),
                            'class_name' => $class->attribute('name'),
                            'attribute_identifier' => $attribute->attribute('identifier'),
                            'attribute_name' => $attribute->attribute('name'),
                            'search_filter' => $classIdentifier . '/' . $attribute->attribute('identifier') . '/name'                            
                        );
					}
				}
			}
			if ( !empty( $return ) )
            {    
                return $return;
            }
			else
				return false;			
		}	

		return false;

    }

?>
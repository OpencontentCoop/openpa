<?php
class ObjectRelationExist
{
	function ObjectRelationExist()
	{
	}

	function createSqlParts($params)
	{
		// first optional param element should be either 'or' or 'and'
		if(!is_numeric($params[0]))
			$clause = array_shift($params);
		else
			$clause = "and";

		// remaining params are pairs of attribute id and object id which should be matched.
		// object id can also be an array of object ids, in that case the match is on either object id.
		$t = 0;
		$sqlCond=array();
		while(sizeof($params) > 0) {
			$attribute_id = array_shift($params);

			//echo $attribute_id."<br>";
            if ( !is_numeric( $attribute_id ) )
            	$attribute_id = eZContentObjectTreeNode::classAttributeIDByIdentifier( $attribute_id );
			if ( $attribute_id === false )
	            eZDebug::writeError( "Unknown attribute identifier", "objectrelationfilter::createSqlParts()" );

			$sqlCond[] = $attribute_id;
			$t++;
		}
//		$sqlJoins="ezcontentobject.id in (select from_contentobject_id from ezcontentobject_link where ".$sqlJoins."   ) and ";

		$sqlJoins=" and ezcl2.contentclassattribute_id in (".implode(",",$sqlCond).") "; 
		$sqlJoins="ezcontentobject.id=ezcl2.from_contentobject_id " . $sqlJoins."   and ";
		return array('tables' => ' ,ezcontentobject_link  ezcl2', 'joins'  => $sqlJoins);
	}
}
?>
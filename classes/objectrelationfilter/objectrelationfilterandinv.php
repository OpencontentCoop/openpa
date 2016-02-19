<?php
class ObjectRelationFilterAndInv
{
	function ObjectRelationFilterAndInv()
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
		$sqlJoinsTo="";
		$sqlJoinsFrom="";
		while(sizeof($params) > 1) {
			$attribute_id = array_shift($params);
			$relatedobject_id = array_shift($params);
            if ( !is_numeric( $attribute_id ) )
            	$attribute_id = eZContentObjectTreeNode::classAttributeIDByIdentifier( $attribute_id );
			if ( $attribute_id === false )
	            eZDebug::writeError( "Unknown attribute identifier", "ObjectRelationFilterAndInv::createSqlParts()" );

			$sqlCondTo = " ( contentclassattribute_id=$attribute_id and to_contentobject_id=$relatedobject_id) ";
			$sqlCondFrom = " ( contentclassattribute_id=$attribute_id and from_contentobject_id=$relatedobject_id) ";


			if($t >= 1){
				$sqlJoinsTo .= $clause .$sqlCondTo ;
				$sqlJoinsFrom .= $clause .$sqlCondFrom ;
			}
			else{
				$sqlJoinsTo .= $sqlCondTo ;
				$sqlJoinsFrom .= $sqlCondFrom ;
				}
			$t++;
		}

		$db =& eZDB::instance();
		$result=$db->arrayQuery("select distinct from_contentobject_id as id from ezcontentobject_link, ezcontentobject
					where current_version=from_contentobject_version AND ezcontentobject.id=ezcontentobject_link.from_contentobject_id AND $sqlCondTo");

		$sql = "select distinct from_contentobject_id from ezcontentobject_link, ezcontentobject
                                                where current_version=from_contentobject_version  AND ezcontentobject.id=ezcontentobject_link.from_contentobject_id AND $sqlJoins";
                eZDebug::writeError('SQL filtro:', $sql);
                $result=$db->arrayQuery($sql);

	 	$result=array_merge($result,$db->arrayQuery("select distinct to_contentobject_id as id from ezcontentobject_link, ezcontentobject
                                                where current_version=from_contentobject_version AND $sqlJoinsFrom") );
		unset($db);
		$liste=Array();
	 	foreach ( $result as $row )
	  	{
     		$liste[]=$row['id'];
  	  	}
  	  	unset($result);
  	  
  	  	if (count($liste) ==0) 
  	  		$liste[]=0;
		$sqlJoins=" ezcontentobject.id in(".implode(",",$liste).") and ";
		return array('tables' => '', 'joins'  => $sqlJoins);

	}
}
?>

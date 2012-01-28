<?php
/*!
	\class   ArraySortOperator arraysortoperator.php
	\ingroup eZTemplateOperators
	\brief   Wrapper for PHP array sort functions
	\version 1.0
	\date    March 2006
	\author  Marc Boon - marcboon[AT]dds[DOT]nl

  Example:
\code
{$array|sort([$flags])}
{$array|rsort([$flags])}
{$array|asort([$flags])}
{$array|arsort([$flags])}
{$array|ksort([$flags])}
{$array|krsort([$flags])}
{$array|natsort()}
{$array|natcasesort()}
\endcode

	$flags is optional, and can be 'regular', 'numeric', 'string', or 'locale_string'.

	See also: http://www.php.net/manual/en/ref.array.php
*/

class ArraySortOperator
{
	/*!
		Constructor, initializes sort flags
	*/
	function ArraySortOperator()
	{
		$this->sort_flags = array(
			'regular' => SORT_REGULAR,
			'numeric' => SORT_NUMERIC,
			'string' => SORT_STRING,
			'locale_string' => SORT_LOCALE_STRING
		);
	}

	/*!
		\return an array with the template operator names.
	*/
	function operatorList()
	{
		return array( 'sort', 'rsort', 'asort', 'arsort', 'ksort', 'krsort', 'natsort', 'natcasesort' );
	}

	/*!
		\return true to tell the template engine that the parameter list exists per operator type,
			this is needed for operator classes that have multiple operators.
	*/
	function namedParameterPerOperator()
	{
		return true;
	}

	/*!
		See eZTemplateOperator::namedParameterList
	*/
	function namedParameterList()
	{
		return array(
			'sort' => array( 'flags' => array( 'type' => 'string', 'required' => false, 'default' => 'regular' ) ),
			'rsort' => array( 'flags' => array( 'type' => 'string', 'required' => false, 'default' => 'regular' ) ),
			'asort' => array( 'flags' => array( 'type' => 'string', 'required' => false, 'default' => 'regular' ) ),
			'arsort' => array( 'flags' => array( 'type' => 'string', 'required' => false, 'default' => 'regular' ) ),
			'ksort' => array( 'flags' => array( 'type' => 'string', 'required' => false, 'default' => 'regular' ) ),
			'krsort' => array( 'flags' => array( 'type' => 'string', 'required' => false, 'default' => 'regular' ) ),
			'natsort' => NULL,
			'natcasesort' => NULL
		);
	}

	/*!
		Executes the PHP function for the operator and modifies \a $operatorValue.
	*/
	function modify( &$tpl, &$operatorName, &$operatorParameters, &$rootNamespace, &$currentNamespace, &$operatorValue, &$namedParameters )
	{
		switch ( $operatorName )
		{
			case 'sort':
			case 'rsort':
			case 'asort':
			case 'arsort':
			case 'ksort':
			case 'krsort':

				$key = $namedParameters['flags'];
				$flags = array_key_exists( $key, $this->sort_flags ) ? $this->sort_flags[ $key ] : 0;

				if( !$operatorName( $operatorValue, $flags ) )
				{
					$operatorValue = false;
				}
				break;

			case 'natsort':
			case 'natcasesort':

				if( !$operatorName( $operatorValue ) )
				{
					$operatorValue = false;
				}
				break;

			default:
					$operatorValue = false;
    }
	}
}

?>

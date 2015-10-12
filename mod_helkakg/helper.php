<?php
class modHelkakgHelper {
	public static function getHelkakg($order) {

		// Obtain a database connection
		$db = JFactory::getDbo();

		switch ($order) {
			case 'latest':
				$orderby = $db->quoteName('time').' DESC';
				break;
			case 'oldest':
				$orderby = $db->quoteName('time').' ASC';
				break;
			case 'ordering':
				$orderby = $db->quoteName('parent').' DESC, '.$db->quoteName('ordering').' ASC';
				break;
			case 'random':
				$orderby = $db->quoteName('time').' DESC';
				break;
		}

		// Retrieve the shout
		$query = $db->getQuery(true)
			->select($db->quoteName('objectid'))
			->from($db->quoteName('#__helkakg'))
			->where('type = ' . $db->Quote('img'))
			->order($orderby);

		// Prepare the query
		$db->setQuery($query);

		// Load the row.
		$result = $db->loadColumn();

		// Return the results
		return $result;
	}
}
?>

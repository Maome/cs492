<?php
	class dbw {
		private $conn;
		private $debug;
		
		// Return value with single quotes on both sides
		public static function singleQuote($value) {
			return '\'' .$value . '\'';
		}
		
		// Return the string NULL if value is empty
		public static function nullIfEmpty($value) {
			return empty($value) ? 'NULL' : $value;
		}
		
		public static function zeroIfEmpty($value) {
			return empty($value) ? 0: $value;
		}
		
		// Constructor
		function dbw($server, $user, $pass, $catalog,$debug = false) {
			$this->conn = new mysqli($server, $user, $pass, $catalog);
			$this->debug = $debug;
			if (!$this->conn)  die("<p>The database server is unavailable.</p>");
		}
						
		// Set debug to true or false. If true debug information will be output for each query executed
		function setDebug($debug) {
			if (is_bool($debug)) $this->debug = $debug;
			else return -1;
		}
		
		// Return the result set from a query
		function query($qry) {
			$rv = $this->conn->query($qry);
			if ($this->debug) $this->printDebug($qry);
			return $rv;
		}
		
		// Return a single column value from a single row
		function queryUnique($table,$value,$where) {
			$qry = sprintf('SELECT %s FROM %s WHERE %s',$value,$table,$where);
			$result = $this->query($qry);
			if ($result->num_rows != 1) return null;
			else {
				$row = $result->fetch_row();
				return $row[0];
			}
		}
		
		// Return an array with the column values from a single row
		function querySingleRow($qry, $isAssociative = false) {
			$result = $this->query($qry);
			if ($result->num_rows != 1) return null;
			else {
				if ($isAssociative) $row = $result->fetch_assoc();
				else $row = $result->fetch_row();
				
				return $row;
			}
		}
		
		// Update the columns in the values array for table based on where
		function updateData($table,$values,$where) {
			$qry = 'UPDATE ' .$table .' SET ';
			$i = 0;
			foreach($values as $key => $value) {
				$qry .= ' ' .$key .' = ' .$value .($i == count($values) - 1 ? '' : ', ');
				$i++;
			}
			$qry .= ' WHERE ' .$where;
			return $this->query($qry);
		}
		
		// Get key value pair array from query which selects two columns
		function queryPairs($qry) {
			$result = $this->query($qry);
			$rv = array();
			while ($row = $result->fetch_row()) {
				$rv[$row[0]] = $row[1];
			}
			return $rv;
		}
		
		// Return value from configuration table
		function configValue($name) {
			return $this->queryUnique('Configuration','Value','Name = ' .$this->singleQuote($name) .' AND RecordStatus <> 3');
		}

		// Output a table based on the results of a query
		function queryToTable($qry, $name, $headers = NULL, $tableClassOverride = NULL, $rowClassOverride = NULL, $rowKeyField = NULL) {
			echo '<table id="' .$name .'" class="table '.(empty($classOverride) ? 'table-striped' : $classOverride) .'">';
			echo '<thead>' .PHP_EOL;
				echo '<tr>';
				$result = $this->query($qry);
				if (!empty($headers)) {
					for($i = 0; $i < count($headers); $i++) echo '<th>' .$headers[$i] .'</th>';
				}
				else {
					$fields = $result->fetch_fields();
					for($i = 0; $i < count($fields); $i++) echo '<th>' .$fields[$i]->name .'</th>';
				}
				echo '</tr>' .PHP_EOL;
			echo '</thead>' .PHP_EOL;
			echo '<tbody>';
				while($row = $result->fetch_array()) {
					//echo ($rowClassOverride == NULL ? '<tr>' : '<tr class="' .$rowClassOverride .'">') .PHP_EOL;
					$id = ($rowKeyField === NULL ? '' : ' id="' .$row[$rowKeyField] .'"');
					$class = ($rowClassOverride == NULL ? '' : ' class="' .$rowClassOverride .'"');
					echo '<tr' .$id .$class .'>';
					for($i = 0; $i < $result->field_count; $i++) {
						if ($i != $rowKeyField) echo '<td>' .$row[$i] .'</td>';
					}
					echo '</tr>' .PHP_EOL;
				}
			echo '</tbody>';
			echo '</table>';
		}

		// Output a table based on the results of a query
		function queryToEditableTable($qry, $tableToEdit, $keyColumn, $name, $action, $editFunction, $updateQry, $headers = NULL) {
			// Delete record
			if (isset($_GET['edit'])) {
				$editFunction($this, $_GET['edit'], $updateQry);
				if (!isset($_POST['edit'])) return;
			}
			else if (isset($_POST['delete'])) {
				$delQry = 'UPDATE ' .$tableToEdit .' SET RecordStatus = 3, RecordStatusDate = NOW() WHERE ' .$keyColumn .' = ' .$_POST[$keyColumn];
				$this->query($delQry);
			}

			echo '<table id="' .$name .'" class="table table-striped">';
			echo '<thead>' .PHP_EOL;
			echo '<tr>' .PHP_EOL;
			$result = $this->query($qry);
			if (!empty($headers)) {
				for($i = 0; $i < count($headers); $i++) echo '<th>' .$headers[$i] .'</th>';
			}
			else {
				$fields = $result->fetch_fields();
				for($i = 0; $i < count($fields); $i++) {
					if ($i != $keyColumn) echo '<th>' .$fields[$i]->name .'</th>';
				}
				echo '<th></th>';
			}
			echo '</tr>' .PHP_EOL;
			echo '</thead>' .PHP_EOL;
			echo '<tbody>';
			$count = 0;
			for($count = 0; $row = $result->fetch_array(); $count++) {
				echo '<tr>' .PHP_EOL;
				for($i = 0; $i < $result->field_count; $i++) {
					if ($i != $keyColumn)	echo '<td>' .$row[$i] .'</td>';
				}
				echo "
				<form class='form-inline' action='" .$action ."' method='POST'>
				<input type='hidden' name='" .$keyColumn ."' id='" .$keyColumn ."' value='" . $row[$keyColumn] . "'>
				<input type='hidden' name='delete' id='delete' value='true'>												
				<td><a href='?edit=" .$row[$keyColumn] ."' role='button' class='btn btn-primary'>Edit</a>
				<input class='btn btn-danger' type='submit' value='Delete'></td>
				</form>";
				echo '</tr>' .PHP_EOL;
			}
			echo '</tbody>';
			echo '</table>';
		}
		
		// Get a string with the error(s) from the last executed query
		function getError() {
			return $this->conn->error;
		}
		
		// Output qry and the current (if any) error associated with dbw
		function printDebug($qry) {
			$error = $this->getError();
			echo '<b>Query:</b> ' .htmlentities($qry) .'<br />';
			echo '<b>Error: </b>' .(empty($error) ? 'N/A' : $error) .'<br />';
		}
	}
?>

<?php 

require_once "./inc/config.inc"; 

session_start(); 
if (!isset($_SESSION['sid'])) {
	$_SESSION['sid'] = md5(time() . $_SERVER['REMOTE_ADDR']); 
}

// protect from free reign of all php ops
global $legal_op; 
$legal_op = array(
	'test', 
	'save', 
	'view', 
	'load_form'
); 

/* DATABASE CONNECTION */
global $dbh; 
$dbh = mysql_connect(DBHOST, DBUSER, DBPASS); 
if (!mysql_select_db(DBNAME, $dbh)) die('Could not select database');

// what do we got from request? Let's get the op and args
function process_request() {
	global $legal_op; 
	
	$result = array(); 
	
	$request = split("/", $_REQUEST['q']); 
	$op = $request[0]; 
	$arg = $request[1];
	 
	if (in_array($op, $legal_op) && is_callable($op, false, $run)) {
		$run($result, $arg);
	} else {
		home($result); 
	}
	
	return $result; 
}

function theme_output($variables) {
	$variables['errors'] = $_SESSION['messages']['error']; 
	$variables['alerts'] = $_SESSION['messages']['alert']; 
	unset($_SESSION['messages']); 

	extract($variables, EXTR_SKIP);  // Extract the variables to a local namespace
	ob_start();                      // Start output buffering
	include "./theme/$template_file.php";      // Include the template file
	$contents = ob_get_contents();   // Get the contents of the buffer
	ob_end_clean();                  // End buffering and discard

	return $contents; 
}

function home(&$var) {
	
	load_form(&$var);
	view(&$var, -1);  
	
	$var['title'] = 'POPE FACT';  
	$var['ads'] = TRUE; 
	$var['template_file'] = 'page'; 
}

function test(&$var, $arg = NULL) {
	$var['template_file'] = 'page'; 
	$var['title'] = 'Test Page'; 
	$var['output'] = 'SUCCESS!'; 
	
	return TRUE; 
}

function load_form(&$var, $arg = NULL) {

	$output .= '
	<label>Please enter a four letter word: </label>
	<input class="fact-input" type="text" id="fact-input" name="fact-input" maxlength="4" />
	<input type="submit" class="fact-input" id="fact-submit" name="fact-submit" value="Go" />
	'; 
	
	$var['template_file'] = 'op'; 
	$var['form'] = $output; 
	
	return TRUE; 
}

function view(&$var, $id = NULL) {

	if ($_REQUEST['refresh'] == 1) {
		$var['template_file'] = 'op'; 
		$id = -1; 
	} else {
		$var['template_file'] = 'page'; 	
	}

	$query; 
	switch($id) {
		case -1: 
			$query = "
SELECT a.id id1, a.fact fact1, DATE_FORMAT(CONVERT_TZ(a.timestamp, 'US/Central', 'US/Pacific'), '%l:%i%p %b %e %Y') nicetime1, 
b.id id2, b.fact fact2, DATE_FORMAT(CONVERT_TZ(b.timestamp, 'US/Central', 'US/Pacific'), '%l:%i%p %b %e %Y') nicetime2
FROM fact as a JOIN fact as b
ON b.fid = a.id 
WHERE a.fact IS NOT NULL AND b.fact IS NOT NULL
ORDER BY a.id DESC
LIMIT 1
			";
			break; 
		case NULL:
			$query = "
SELECT a.id id1, a.fact fact1, DATE_FORMAT(CONVERT_TZ(a.timestamp, 'US/Central', 'US/Pacific'), '%l:%i%p %b %e %Y') nicetime1, 
b.id id2, b.fact fact2, DATE_FORMAT(CONVERT_TZ(b.timestamp, 'US/Central', 'US/Pacific'), '%l:%i%p %b %e %Y') nicetime2
FROM fact as a JOIN fact as b
ON b.fid = a.id 
WHERE a.fact IS NOT NULL AND b.fact IS NOT NULL
ORDER BY a.id DESC
			";
			break; 
		default:
			$query = sprintf("
SELECT a.id id1, a.fact fact1, DATE_FORMAT(CONVERT_TZ(a.timestamp, 'US/Central', 'US/Pacific'), '%%l:%%i%%p %%b %%e %%Y') nicetime1, 
b.id id2, b.fact fact2, DATE_FORMAT(CONVERT_TZ(b.timestamp, 'US/Central', 'US/Pacific'), '%%l:%%i%%p %%b %%e %%Y') nicetime2
FROM fact as a JOIN fact as b
ON b.fid = a.id 
WHERE a.fact IS NOT NULL AND b.fact IS NOT NULL
AND (a.id = %d OR b.id = %d)
			", 
			mysql_real_escape_string($id), 
			mysql_real_escape_string($id)
			);
			$var['ads'] = TRUE; 
			break; 
	}
	$result = mysql_query($query); 
	
	$list; 
	$count = 0; 
	while ($row = mysql_fetch_assoc($result)) {
		$list .= '
<ul class="popefact">
	<li class="word first">
		' . htmlentities($row['fact1']) . ' 
	</li>
	<li class="word second">
		' . htmlentities($row['fact2']) . ' 
	</li>
</ul>	
<ul class="meta">
	<li><a href="/view/' . $row['id1'] . '" title="permalink">#</a></li>
	<li>Word 1 submitted ' . $row['nicetime1'] . '</li>
	<li>Word 2 submitted ' . $row['nicetime2'] . '</li>
</ul>	
		'; 
		$title = $row['fact1'] . ' ' . $row['fact2']; 
		$count++; 
	} 
	if (empty($list)) {
		set_message('No Pope Fact Found', 'error'); 
		$title = 'No Pope Fact Found'; 
	} else {
		$output = '<div class="popefacts">' . $list . '</div>'; 
	}

	$var['count'] = $count; 
	$var['title'] = $title; 
	$var['output'] = $output; 
}

function save(&$var, $new_fact = NULL) {
	global $dbh; 
	
	// did we get an ajax request with teh word in the post?
	if ($new_fact == NULL) {
		$new_fact = $_REQUEST['value']; 
	}
	
	// authorize the user based on ip, session, input format, etc. 
	if (!_auth($new_fact)) {
		load_form($var); 
		return FALSE; 
	}

	$new_uid = 0; 
	$new_ip = $_SERVER['REMOTE_ADDR']; 
	$new_session = $_SESSION['sid']; 

	$new_fid = NULL; 
	// Get the last submitted fact word
	$query = "
SELECT id, fid FROM fact
ORDER BY id DESC
LIMIT 1	
	";
	$result = mysql_query($query); 
	// Is the last submitted fact word unpaired? 
	while ($row = mysql_fetch_assoc($result)) {
		$new_fid = $row['fid'] == 0 ? $row['id'] : NULL; 
		// If unpaired, pair it with a backwards fid reference
	}
	
	$query = sprintf("
INSERT INTO fact (uid, fact, fid, ip, session) 
VALUES (%d, '%s', %d, '%s', '%s') 
	", 
	mysql_real_escape_string($new_uid),
	mysql_real_escape_string($new_fact),
	mysql_real_escape_string($new_fid),
	mysql_real_escape_string($new_ip), 
	mysql_real_escape_string($new_session)
	);	
	$result = mysql_query($query);
	
	if ($result) {
		set_message('Fact word submitted successfully');
		$var['template_file'] = 'op'; 
		load_form($var); 
	} else {
		set_message('Fact failed to submit. Please try again.'); 
		load_form($var); 
	}
	
 	return TRUE; 
}

/*
	_auth function
	Make sure the user is allowed to submit a pope fact at this moment
	Make sure the fact is a valid fact
*/
function _auth($word) {
	
	// Get the last submitted fact word
	$query = "
SELECT id, fid, ip, session FROM fact
ORDER BY id DESC
LIMIT 1	
	";
	$result = mysql_query($query); 
	while ($row = mysql_fetch_assoc($result)) {
		if (
			$_SESSION['sid'] == $row['session'] 
			&& $row['fid'] == 0 
		) {
			set_message("You may not submit another fact until someone else has submitted.", 'error'); 
			return FALSE; 
		}
	}
	if (!ctype_alnum($word) || strlen($word) != 4) {
		set_message("Please only submit a valid four-letter word.", 'error'); 
		return FALSE; 
	}
	
	return TRUE; 
}

function set_message($msg, $type = 'alert') {
	if (!empty($msg)) {
		$_SESSION['messages'][$type][] = $msg; 
	}
	
	return TRUE; 
}
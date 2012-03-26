<?php 

require_once('/inc/func.php'); 

function save_word() {
	global $dbh; 
	
	// authorize the user based on ip, session, input format, etc. 
	if (!_auth()) {
		return FALSE; 
	}

	$new_fact = $_REQUEST['fact']; 
	$new_uid = 0; 
	$new_ip = 
	$item = $_REQUEST['item'];
	$name = $_REQUEST['name'];
	$value = $_REQUEST['value'];
	$id = $_REQUEST['id'];
	
	// 
	$new_fid = NULL; 
	$query = "
SELECT id, fid FROM fact
ORDER BY id DESC
LIMIT 1	
	";
	$result = mysql_query($query); 
	while ($row = mysql_fetch_assoc($result)) {
		$new_fid = $row['fid'] == 0? NULL : $row['fid']; 
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
	
	if ($result) return TRUE; 
	
	set_message('Fact failed to submit. Please try again.', 'error'); 
	
	return FALSE; 
}

if (is_callable($_REQUEST['op'], false, $run)) {
	$output = $run();
} else {
	$output = "Function (".$_REQUEST['op'].") not found.";
}

print $output;
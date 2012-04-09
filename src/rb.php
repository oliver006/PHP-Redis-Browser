<?php

/***************************************************************************
 *
 *
 *                PHP Redis Broser v0.1
  
    a simple, one-file php based browser for your Redis database  
  
    Try a demo at http://ohardt.com/php-rb/demo.php

    
 * 
 *  
 * LICENSE  
 * 
 *  

Copyright (c) 2012         Contact: ohardt at gmail dot com

Permission is hereby granted, free of charge, to any person
obtaining a copy of this software and associated documentation
files (the "Software"), to deal in the Software without
restriction, including without limitation the rights to use,
copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the
Software is furnished to do so, subject to the following
conditions:

The above copyright notice and this permission notice shall be
included in all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
OTHER DEALINGS IN THE SOFTWARE.






 * 
 * 
 * IMPORTANT
 * 
 *   

Requires the excellent predis library
Get it here: https://github.com/nrk/predis/

Adjust the path to the library as needed

*/

require 'lib/Predis/Autoloader.php';
Predis\Autoloader::register();






/***************************************************************************
 * 
 * 
 * TODO
 * 
 *   - more server commands
 *   - quick commands
 *   - password support
 *   - pagination of results
 *   - nicer data screen
 *   - improve layout
 *   - clean up html/php mix/mess
 *   - ...
 * 
 */
	



 	
/***************************************************************************
 * 
 * 
 * CONFIG START 
 * 
 * enter your redis server details here
 * 
 */

	$CONFIG_HOST = array(
	
	    'host'     		=> '127.0.0.1', 
	    'port'    	 	=> 6379, 
	    'default_db' 	=> 0
	);
	
	
	
	
/***************************************************************************
 * 
 * 
 * CONFIG END
 * 
 * no need to change anything beyond this point  
 * 
 */
		
		
		
		
		
		
		
		
		error_reporting( -1 );
		ini_set( 'display_errors',  '1');
	    ini_set( "log_errors",      "on" );
		
		
		
		
		
		
		
		
		
		
		
		
	$redis   		= new Predis\Client( $CONFIG_HOST );
	$is_demo 		= false;
	$script_name 	= isset( $_SERVER['SCRIPT_NAME'] ) ? $_SERVER['SCRIPT_NAME'] : "rb.php";


/***************************************************************************
 * 
 * select database
 * ( demo version allows access to DB #0 only )
 *  
 */
 
	if( $is_demo ) {
	    
	    $db_num = 0;
	    
	} else {
	
		$db_num = $CONFIG_HOST['default_db'];
		if( isset( $_REQUEST["d"] ) ) {
			
			if( !is_numeric( $_REQUEST["d"] ) ) die;
		    $db_num = (int)$_REQUEST["d"];
		}
	}
	
    $redis->select( $db_num );






/***************************************************************************
 * 
 * set up action, sort and pattern
 *  
 */
	
    $action = "b";
    
	if( isset( $_REQUEST['a'] ) ) {
	    $action = $_REQUEST['a'];
	}    
	
	$sort    = "no";
	if( isset( $_REQUEST["s"] ) ) {
		
		if( ( $_REQUEST["s"] !== "ttl" ) &&
			( $_REQUEST["s"] !== "key" ) && 
			( $_REQUEST["s"] !== "sz"  ) && 
			( $_REQUEST["s"] !== "1"   ) &&            // "1" is for sorting sets, lists 
			( $_REQUEST["s"] !== "no"  ) ) {
		    die;
		} 
		
		$sort = $_REQUEST["s"];
	}
	
	$pattern = "*";
	if( isset( $_REQUEST["p"] ) ) {
		
	    $pattern = $_REQUEST["p"];
	}
	
	
	




/***************************************************************************
 * 
 * handle action
 *  
 */
	
	switch( $action ) {
		
	    case "s": {   // show

		
			if( isset( $_REQUEST["k"] ) ) {
				$k = base64_decode( $_REQUEST["k"] );
				
				display_key( $k );
				die;
			}
		
			break;
	    }

		
	    case "as": {   // add string
	    	
	    	if( ( isset( $_REQUEST['key'] ) ) && 
	    		( isset( $_REQUEST['val'] ) ) ) {
	        	$redis->set( $_REQUEST['key'], $_REQUEST['val'] );
    		}
	        break;
	    }
	    case "ah": {   // add hash
	    
	    	if( ( isset( $_REQUEST['key']  ) ) && 
	    		( isset( $_REQUEST['val']  ) ) &&
	    		( isset( $_REQUEST['hash'] ) ) ) {
		        $redis->hset( $_REQUEST['hash'], $_REQUEST['key'], $_REQUEST['val'] );
    		}
	        break;
	    }
        case "d": {   // delete

			if( ( isset( $_REQUEST['sel'] ) )    && 
			    ( count( $_REQUEST['sel'] ) > 0 ) ) {
			    	
				foreach( $_REQUEST['sel'] as $s ) {
	            	$redis->del( base64_decode( $s ) );
				}
		    }
            break;
        }
        
        case "f": {   // flush DB

        	$redis->flushdb();
            break;
        }
        
        case "p": {    // make persistent

			if( ( isset( $_REQUEST['sel'] ) )    && 
			    ( count( $_REQUEST['sel'] ) > 0 ) ) {
			    	
				foreach( $_REQUEST['sel'] as $s ) {
		            $redis->persist( base64_decode( $s ) );
				}
		    }            
            break;
        }
	    
	}
		
	
/***************************************************************************
 * 
 * 
 * done with actions - display the header section 
 *
 */	 


?>


<html>
<head>
  <title>PHP Redis Browser 0.1</title>
</head>
<body>
<style type="text/css">

td {
    background-color: 	#FFFFFF;
    font-family:		"Arial";
    font-size:			10pt;
}

th {
    background-color: 	#FFFFFF;
    font-family:		"Arial";
    font-size:			10pt;
    font-weight:		bold;
}

body {
    font-family:		"Arial";
    font-size:			10pt;
}

A:link 		{text-decoration: none;      	color: #0000ff; 	}
A:visited 	{text-decoration: none;   		color: #0000ff; 	}
A:active 	{text-decoration: none;    		color: #0000ff;     } 	
A:hover 	{text-decoration: underline; 	color: red;    		}  

</style>

<center>Databases:&nbsp;&nbsp;

<?php


	$dbs = array();
		
	if( $is_demo ) {

		// only db #0 avail in demo mode	    
		$dbs = array( 0 => array() );
	    
	} else {

		$dbs = util_get_dbs( $redis );
	}
	
	foreach( $dbs as $n => $db_info ) {
		
		if( $n == $db_num ) echo "<b>";
		
	    echo 	"<a href='" . $script_name . 
				"?d=" . $n . "&sort=" . $sort . 
				"&p=" . htmlspecialchars( $pattern ) . "'>[ #" . $n . " ]</a>&nbsp;&nbsp;&nbsp;";
				
		if( $n == $db_num ) echo "</b>";
	}
	
	
	echo "<a href='" . $script_name . "?info=1'>[ Info ]</a> ";
	echo "<br/><br/>";

	// show info? 
	if( isset( $_REQUEST['info'] ) ) {

		display_info();		
		die;	    
	}





	echo '<form name="search" method="get" action="' . $script_name . '">';
	echo 'Pattern <input type="text" name="p" value="' . $pattern . '" />';
	echo '<input type="submit" value="Search" /> ';

	echo 'sort by: <input type="radio" name="s"  value="key"' . ( $sort == 'key' ? "checked" : "" ) .  ' />Key ';
	echo '<input type="radio" name="s" value="sz" ' . ( $sort == 'sz' ? "checked" : "" ) .  ' />Size ';
	echo '<input type="radio" name="s" value="ttl" ' . ( $sort == 'ttl' ? "checked" : "" ) .  ' />TTL ';
	echo '<input type="radio" name="s"  value="no"' . ( $sort == 'no' ? "checked" : "" ) .  ' />No ';

	echo '<input type="hidden" name="d" value="' . $db_num . '" />';
	
	echo '</form></center>' . "<br/>\n";
	
	
	
	
	
/***************************************************************************
 * 
 * 
 * done with the header section - display the keys 
 *
 */	 	


	$count_all_keys_in_db = $redis->dbsize();
	
	$all_keys 		= array();
	$matched_keys   = $redis->keys( $pattern );
	
	foreach( $matched_keys as $k ) {
	    
	    $sz = -1;
	    
	    $type = $redis->type( $k );
	    $ttl  = $redis->ttl ( $k );
	    
	    if( $type == "string" ) {
	        $sz = $redis->strlen( $k );
	    } else if( $type == "hash" ) {
	        $sz = $redis->hlen( $k );
	    } else if( $type == "set" ) {
	        $sz = $redis->scard( $k );
	    }
	    
	    if( !isset( $all_keys[$type] ) ) {
	        $all_keys[$type] = array();
	    }
	    
	    array_push( $all_keys[$type], array( "key" => $k, "ttl" => $ttl, "sz" => $sz ) );
	}
	
	// sort by type
	ksort( $all_keys );


	util_html_form_start( "form_select", $db_num, $pattern, $sort, "post", false );

	echo "Showing " . count( $matched_keys ) . " of " . $count_all_keys_in_db . " keys.";
?>	
&nbsp;&nbsp;
<select name="a">
	<option value="d">Delete selected</option>
	<option value="p">Persist selected</option>
	<option value="f">Flush DB</option>
	</select>
<input type="submit" value="Execute" onClick="return confirmSubmit()" /><br/><br/>


<table border="0" cellpadding="2" cellspacing="1" width="70%" bgcolor="#000000">
<tr>
	<th align=center  width='40'> 
		<input type="checkbox" name="check_all" value="Check All" onClick="javascript:selectToggle('form_select');">
	</th>
	<th align=left width='50'> Type </th>
	<th align=left > Key </th>
	<th align=center width='75'> Size </th>
	<th align=center width='75'> TTL </th>
</tr>



<?php
		
	foreach( $all_keys as $type => $keys ) {
	
		switch( $sort ) {
		    
		    case "key": uasort( $keys, 'util_custom_cmp_key' ); break;		    
			case "ttl": uasort( $keys, 'util_custom_cmp_ttl' ); break;		    
			case "sz" : uasort( $keys, 'util_custom_cmp_sz' );  break;		    
		} 
	
	
		foreach( $keys as $k ) {
			
			$ttl_txt = util_format_ttl( $k['ttl'] );
			
			$sz_txt  = util_format_size( $type, $k['sz'] );
			
	    	echo '<tr><td align=center><input type="checkbox" name="sel[]" value="' . htmlspecialchars( base64_encode( $k['key'] ) ) . '" />' . "</td>";
			echo "<td>" . $type . "</td><td><a href='" . $script_name . "?a=s&d=" . $db_num . "&k=" . htmlspecialchars( base64_encode( $k['key'] ) ) .  "'>" . $k['key'] . "</a> </td>";
			echo "<td  align=center>" . $sz_txt . "</td>";
			echo "<td  align=center>" . $ttl_txt . "</td>";			
			echo "</tr>\n";
		}
	    
	}
	echo "</table>";
	echo "</form>";	
	echo "<br/><br/>";


	util_html_form_start( "as", $db_num, $pattern, $sort );
?>
	<input type="text" name="key" value="<key>"			onfocus="this.value==this.defaultValue?this.value='':null"/>
	<input type="text" name="val" value="<value>"		onfocus="this.value==this.defaultValue?this.value='':null"/>
	<input type="submit" value="Create String" /><br/>
	</form>
<?php
	util_html_form_start( "ah", $db_num, $pattern, $sort );
?>
	<input type="text" name="hash" 	value="<key>" 		onfocus="this.value==this.defaultValue?this.value='':null"/>
	<input type="text" name="val" 	value="<field>"		onfocus="this.value==this.defaultValue?this.value='':null"/>
	<input type="text" name="key" 	value="<value>"		onfocus="this.value==this.defaultValue?this.value='':null"/>
	<input type="submit" value="Create Hash" /><br/>
	</form>

<SCRIPT LANGUAGE="JavaScript">

function selectToggle(n) {

	var fo = document.forms[n];

	t = fo.elements['check_all'].checked;

     for( var i=0; i < fo.length; i++ )  {
     
     	if( fo.elements[i].name == 'check_all' ) {
     		continue;
 		}
     
     	if( t ) { 
     		fo.elements[i].checked = "checked";      
     	} else {    
     		fo.elements[i].checked = "";
 		}
     }    
}     

function confirmSubmit() {
	
	if( document.form_select.a.selectedIndex !== 2 ) {
	    return true;
	}

	return confirm("Are you sure you wish to continue?");
}


</script>


</body>
</html>







<?php


/***************************************************************************
 * 
 * util functions
 * 
 * 
 */


	function display_info() {
	    
		global $redis;
		


		$ts_lastsave = $redis->lastsave();
		$secs = time() - $ts_lastsave;
		
		echo "<center>Last save " . $secs . " seconds ago. <br/><br/>";	
	
		
		
		$info = $redis->info();
		echo '<table border="0" cellpadding="3" cellspacing="1" width="50%" bgcolor="#000000">';
			
		foreach( $info as $k => $v ) {
		
			if( $k == 'allocation_stats' ) {
			    $v = str_replace( ",", "<br/>", $v );
			}
			
			if( substr( $k, 0, 2 ) == "db" ) {
				$v = "Keys: " . $v['keys'] . "<br/>Expires: " . $v['expires'];
			}
		
	    	echo '<tr><td>' .  $k . "</td>";
	    	echo '<td>' .  $v . "</td></tr>";
		    
		}
		echo "</table>";
	    
	}




	function display_key( $k ) {
		
		global $redis;
		
		$type   = $redis->type( $k );
		$retval = false;
	
		echo "<pre>";
	
		switch( $type ) {
	
			case "string": {
				
				$retval = $redis->get( $k );
				break;
			}   

			case "hash": {
				
				$retval = $redis->hgetall( $k );
				break;
			}		

			case "list": {
				
				$retval = $redis->lrange( $k, 0, -1 );
				break;
			}
			
			case "set": {
				
				$retval = $redis->smembers( $k );
				break;
			}
				
			case "zset": {
				
				$retval = $redis->zrange( $k, 0, -1, "WITHSCORES" );
				break;
			}
				
			default: {
			    $retval = "Data type not supported (yet)";
			    break;
			}	
		}
		
		
		
		echo "Key:  " . $k 		. "\n";
		echo "Type: " . $type 	. "\n";

		// unserialize?	
		if( isset( $_REQUEST["u"] ) ) {
			
			$retval = unserialize( $retval );
			
			echo "Unserialized\n";
			
		} else {
			if( isset( $_SERVER['REQUEST_URI'] ) ) {
				$u = $_SERVER['REQUEST_URI'] . "&u=1";
			    echo "<a href='". $u . "'>Unserialize</a>\n";
			}
		}
		
		
		if( isset( $_REQUEST["s"] ) ) {
			
			asort( $retval );
			
			echo "Sorted by values\n";
			
		} else {
			if( isset( $_SERVER['REQUEST_URI'] ) ) {
				$u = $_SERVER['REQUEST_URI'] . "&s=1";
			    echo "<a href='". $u . "'>Sort array by values</a>\n";
			}
		}


		echo "\n";
		
		var_dump( $retval );
	}




	function util_get_dbs( $r ) {
	    
	    $info = $r->info();
	    $res = array();
	    foreach( $info as $k => $v ) {
	        
	        if( substr( $k, 0, 2 ) == "db" ) {
	        	$db_num = substr( $k, 2, strlen( $k ) );
	        	
	        	if( is_numeric( $db_num ) ) {
	            	$res[ (int)$db_num ] = $v;
	        	}
	        }
	    }
		return $res;	    
	}


	function util_format_ttl( $ttl ) {

		if( $ttl === -1 ) {
			return $ttl;
		}
		
		$m = ((int)( ( $ttl ) / 60 ));
		
		if( $m > 120 ) {
			$m = ( (int) ( $m / 60 ) ); 
			$s = "" . $m . "h";
		} else {
		    $s = "" . $m . "min";
		}

		$s = $ttl . " (" . $s . ")";
	    
	    return $s;
	}
	
	
	function util_format_size( $type, $sz ) {
		
		$s = $sz; 
		
		if( ( $type === "string" ) &&  
			( (int)$sz > 1100 ) ) {
		    $s = (int)( $sz / 1024 ) . "kb";
		}	    
		
		return $s;
	}

	function util_custom_cmp_key( $a, $b ) {
	    
	    return strcmp( $a['key'], $b['key'] );
	}
		
	function util_custom_cmp_ttl( $a, $b ) {
	    
	    if( $a['ttl'] === $b['ttl'] ) {
	        return util_custom_cmp_key( $a, $b );
	    }
	    
	    return ( $a['ttl'] - $b['ttl'] );
	}
		
	function util_custom_cmp_sz( $a, $b ) {
	    
	    if( $a['sz'] === $b['sz'] ) {
	        return util_custom_cmp_key( $a, $b );
	    }
	    
	    return ( $a['sz'] - $b['sz'] );
	}		
		
	function util_html_form_start( $action, $db_num, $pattern, $sort, $type = "post", $put_action = true ) {
		
		global $script_name;
		
		echo '<form name="' . $action . '" method="' . $type . '" action="' . $script_name . '">';
		if( $put_action ) echo '<input type="hidden" name="a" value="' . $action . '" />';
		echo '<input type="hidden" name="d" value="' . $db_num . '" />';
		echo '<input type="hidden" name="p" value="' . htmlspecialchars( $pattern ) . '" />';	    
		echo '<input type="hidden" name="s" value="' . htmlspecialchars( $sort ) . '" />';	    
	}	



		
	
	

?>





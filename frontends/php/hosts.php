<?php
	include "include/config.inc.php";
	$page["title"] = "Hosts";
	$page["file"] = "hosts.php";
	show_header($page["title"],0,0);
?>

<?php
	if(!check_right("Host","U",0))
	{
		show_table_header("<font color=\"AA0000\">No permissions !</font>");
		show_footer();
		exit;
	}
?>

<?php
	if(isset($HTTP_GET_VARS["register"]))
	{
		if($HTTP_GET_VARS["register"]=="add")
		{
			$result=add_host($HTTP_GET_VARS["host"],$HTTP_GET_VARS["port"],$HTTP_GET_VARS["status"],$HTTP_GET_VARS["template"],$HTTP_GET_VARS["useip"],$HTTP_GET_VARS["ip"],$HTTP_GET_VARS["host_templateid"],$HTTP_GET_VARS["newgroup"],$HTTP_GET_VARS["groups"]);
			show_messages($result,"Host added","Cannot add host");
			unset($HTTP_GET_VARS["hostid"]);
		}
		if($HTTP_GET_VARS["register"]=="update")
		{
			$result=update_host($HTTP_GET_VARS["hostid"],$HTTP_GET_VARS["host"],$HTTP_GET_VARS["port"],$HTTP_GET_VARS["status"],$HTTP_GET_VARS["useip"],$HTTP_GET_VARS["ip"],$HTTP_GET_VARS["newgroup"],$HTTP_GET_VARS["groups"]);
			show_messages($result,"Host details updated","Cannot update host details");
			unset($HTTP_GET_VARS["hostid"]);
		}
		if($HTTP_GET_VARS["register"]=="changestatus")
		{
			$result=update_host_status($HTTP_GET_VARS["hostid"],$HTTP_GET_VARS["status"]);
			show_messages($result,"Host status updated","Cannot update host status");
			unset($HTTP_GET_VARS["hostid"]);
		}
		if($HTTP_GET_VARS["register"]=="delete")
		{
			$result=delete_host($HTTP_GET_VARS["hostid"]);
			show_messages($result,"Host deleted","Cannot delete host");
			unset($HTTP_GET_VARS["hostid"]);
		}
	}
?>

<?php
	show_table_header("CONFIGURATION OF HOSTS");
?>

<?php
	if(!isset($HTTP_GET_VARS["hostid"]))
{
	echo "<TABLE BORDER=0 COLS=4 align=center WIDTH=100% BGCOLOR=\"#CCCCCC\" cellspacing=1 cellpadding=3>";
	echo "<TR>";
	echo "<TD WIDTH=3% NOSAVE><B>Id</B></TD>";
	echo "<TD WIDTH=10% NOSAVE><B>Host</B></TD>";
	echo "<TD WIDTH=10% NOSAVE><B>Port</B></TD>";
	echo "<TD WIDTH=10% NOSAVE><B>Status</B></TD>";
	echo "<TD WIDTH=10% NOSAVE><B>Actions</B></TD>";
	echo "</TR>";

	$result=DBselect("select h.hostid,h.host,h.port,h.status from hosts h order by h.host");
	$col=0;
	while($row=DBfetch($result))
	{
        	if(!check_right("Host","R",$row["hostid"]))
		{
			continue;
		}
		if($col++%2==0)	{ echo "<TR BGCOLOR=#EEEEEE>"; }
		else 		{ echo "<TR BGCOLOR=#DDDDDD>"; }
	
		echo "<TD>".$row["hostid"]."</TD>";
		echo "<TD><a href=\"items.php?hostid=".$row["hostid"]."\">".$row["host"]."</a></TD>";
		echo "<TD>".$row["port"]."</TD>";
		echo "<TD>";
        	if(check_right("Host","U",$row["hostid"]))
		{
			if($row["status"] == 0)	
				echo "<a href=\"hosts.php?hostid=".$row["hostid"]."&register=changestatus&status=1\">Monitored</a>";
			else if($row["status"] == 1)
				echo "<a href=\"hosts.php?hostid=".$row["hostid"]."&register=changestatus&status=0\">Not monitored</a>";
			else if($row["status"] == 2)
				echo "Unreachable";
			else if($row["status"] == 3)
				echo "Template";
			else
				echo "Unknown";
		}
		else
		{
			if($row["status"] == 0)	
				echo "Monitored";
			else if($row["status"] == 1)
				echo "Not monitored";
			else if($row["status"] == 2)
				echo "Unreachable";
			else if($row["status"] == 3)
				echo "Template";
			else
				echo "Unknown";
		}
		echo "</TD>";
        	if(check_right("Host","U",$row["hostid"]))
		{
			echo "<TD><A HREF=\"hosts.php?register=change&hostid=".$row["hostid"]."#form\">Change</A></TD>";
		}
		else
		{
			echo "<TD>Change</TD>";
		}
		echo "</TR>";
	}
	echo "</TABLE>";
}
?>

<?php
	if(isset($HTTP_GET_VARS["register"]) && ($HTTP_GET_VARS["register"] == "change"))
	{
		$result=DBselect("select host,port,status,useip,ip from hosts where hostid=".$HTTP_GET_VARS["hostid"]); 
		$host=DBget_field($result,0,0);
		$port=DBget_field($result,0,1);
		$status=DBget_field($result,0,2);
		$useip=DBget_field($result,0,3);
		$ip=DBget_field($result,0,4);

		if($useip==0)
		{
			$useip="";
		}
		else
		{
			$useip="checked";
		}
	}
	else
	{
		$host="";
		$port=10000;
		$status=0;
		$useip="";
		$ip="";
	}

	echo "<br>";
	echo "<a name=\"form\"></a>";
	show_table2_header_begin();
	echo "Host details";

	show_table2_v_delimiter();
	echo "<form method=\"get\" action=\"hosts.php\">";
	if(isset($HTTP_GET_VARS["hostid"]))
	{
		echo "<input class=\"biginput\" name=\"hostid\" type=\"hidden\" value=\"".$HTTP_GET_VARS["hostid"]."\">";
	}
	echo "Host";
	show_table2_h_delimiter();
	echo "<input class=\"biginput\" name=\"host\" value=\"$host\" size=20>";

	show_table2_v_delimiter();
	echo "Groups";
	show_table2_h_delimiter();
	echo "<select multiple class=\"biginput\" name=\"groups[]\" size=\"5\">";
	$result=DBselect("select distinct groupid,name from groups order by name");
	while($row=DBfetch($result))
	{
		if(isset($HTTP_GET_VARS["hostid"]))
		{
			$sql="select count(*) as count from hosts_groups where hostid=".$HTTP_GET_VARS["hostid"]." and groupid=".$row["groupid"];
			$result2=DBselect($sql);
			$row2=DBfetch($result2);
			if($row2["count"]==0)
			{
				echo "<option value=\"".$row["groupid"]."\">".$row["name"];
			}
			else
			{
				echo "<option value=\"".$row["groupid"]."\" selected>".$row["name"];
			}
		}
		else
		{
			echo "<option value=\"".$row["groupid"]."\">".$row["name"];
		}
	}
	echo "</select>";

	show_table2_v_delimiter();
	echo "New group";
	show_table2_h_delimiter();
	echo "<input class=\"biginput\" name=\"newgroup\" value=\"\" size=20>";

	show_table2_v_delimiter();
	echo "Use IP address";
	show_table2_h_delimiter();
	echo "<INPUT TYPE=\"CHECKBOX\" class=\"biginput\" NAME=\"useip\" $useip>";

	show_table2_v_delimiter();
	echo "IP address";
	show_table2_h_delimiter();
	echo "<input class=\"biginput\" name=\"ip\" value=\"$ip\" size=15>";

	show_table2_v_delimiter();
	echo "Port";
	show_table2_h_delimiter();
	echo "<input class=\"biginput\" name=\"port\" size=6 value=\"$port\">";

	show_table2_v_delimiter();
	echo "Status";
	show_table2_h_delimiter();
	echo "<select class=\"biginput\" name=\"status\" size=\"1\">";
	if($status==0)
	{
		echo "<option value=\"0\" selected>Monitored";
		echo "<option value=\"1\">Not monitored";
		echo "<option value=\"3\">Template";
	}
	else if($status==3)
	{
		echo "<option value=\"0\">Monitored";
		echo "<option value=\"1\" selected>Not monitored";
		echo "<option value=\"3\" selected>Template";
	}
	else
	{
		echo "<option value=\"0\">Monitored";
		echo "<option value=\"1\" selected>Not monitored";
		echo "<option value=\"3\">Template";
	}
	echo "</select>";

	show_table2_v_delimiter();
	echo "Add parameters supported by zabbix_agent";
	show_table2_h_delimiter();
	echo "<INPUT class=\"biginput\" NAME=\"template\" TYPE=\"CHECKBOX\"  VALUE=\"true\" CHECKED>";

	show_table2_v_delimiter();
	echo "Use the host as a template";
	show_table2_h_delimiter();
	echo "<select class=\"biginput\" name=\"host_templateid\" size=\"1\">";
	echo "<option value=\"0\" selected>...";
	$result=DBselect("select host,hostid from hosts where status=3 order by host");
	while($row=DBfetch($result))
	{
		echo "<option value=\"".$row["hostid"]."\">".$row["host"];
	}
	echo "</select>";

	show_table2_v_delimiter2();
	echo "<input type=\"submit\" name=\"register\" value=\"add\">";
	if(isset($HTTP_GET_VARS["hostid"]))
	{
		echo "<input type=\"submit\" name=\"register\" value=\"update\">";
		echo "<input type=\"submit\" name=\"register\" value=\"delete\">";
	}

	show_table2_header_end();
?>

<?php
	show_footer();
?>

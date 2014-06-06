<style>
html {
	padding-bottom: 30px;
}
#debugbar {
	width: 100%;
	margin: 0px;
	padding: 0;
	border-top:1px solid #7f7f7f;
	background-color: #e9e9e9;

	position:fixed;
	bottom:0;
	left:0;
	right:0;
}
#debugbar ul{
	height: 30px;
	position: relative;
	top: 0;
	margin: 0;
	padding: 0;
}
#debugbar li {
	list-style: none;
	float: left;
	margin: 0;
	line-height: 30px;
	padding: 3px 5px;
	font-weight: bold;
	color: #7f7f7f 	;
}
#debugbar li:first-child {
	display:none;
	color: black;
}
#debugbar li:nth-child(2) {
	margin: 0 15px;
	color: black;
}
#debugbar li.active,
#debugbar li:hover
{
	color: #ff8103;
}

#debugbar .right {
	float: right;
}

#debugshow {
	float: left;
	height: 250px;
	width: 99%;
	margin: 0;
	display:none;

	background: white;

	overflow-y: auto;
	padding: 0;
}
#debugdata {
	display:none;
}
#debugshow .list {
	width: 100%;
}
#debugshow .list th{
	width: 200px;
	text-align: right;
	padding: 3px 4px;
	font-weight: bold;
}
#debugshow .list td{
	padding: 3px 10px 3px 3px;
}
#debugshow .list tr:nth-child(2n) {
	background:#f4f4f4;
}
</style>

<script type="text/javascript">
function display_debug(name) {
	document.getElementById("debugshow").style.display = "block";
	document.getElementById("closebutton").style.display = "block";
	document.getElementById("debugshow").innerHTML = document.getElementById(name).innerHTML;
	<?=$js_tab_list?>
	document.getElementById(name+"_tab").className = "active";
}
function close_debug() {
	document.getElementById("debugshow").style.display = "none";
	document.getElementById("closebutton").style.display = "none";
}
</script>

<div id="debugbar">
	<ul>
		<li id="closebutton" onClick="close_debug();">☓</li>
		<?=$tab_list?>
		<li class="right"><?=$run_time?> ms</li>
		<li class="right"><?=$memory?> MB</li>
	</ul>
	<div id="debugshow">
	</div>
	<div id="debugdata">
		<?=$datalist?>
	</div>
</div>


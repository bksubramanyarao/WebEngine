<?php include("includes/header.php"); ?>
<div id="content">
	<ul id="adminmenu">
		<li><a href="{{Link|Get|/admin/tools/populate-db/}}">populate db</a></li>
		<li><a href="{{Link|Get|/admin/tools/nuke-db/}}">nuke db</a></li>
	</ul>
	table
	<select name="table">
		<option value="">- Select table -</option>
		<?php foreach ($tables as $t) { ?>
			<option value="<?php echo $t; ?>"><?php echo $t; ?></option>
		<?php } ?>
	</select>
	<div id="tablecontent">
		
	</div>
	<div id="breadcrumb">

	</div>
	<div id="detailform">
	
	</div>
</div>
<?php include("includes/footer.php"); ?>
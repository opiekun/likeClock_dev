<?php require_once("head.php")?>
<div class="container_12">
	<div class="grid_12 title ">
		<div class="logo">
			<div class="logoauth">CPMagento Importer</div>
		</div>
		<div class="info" style="width: 400px">
			<h3>Release Information</h3>
			<div class="version">
 				v<?php echo Magmi_Version::$version?>
 			</div>
			<div class="author">
				Provided to the community by <b><i><a
						href="mailto:dweeves@gmail.com">Dweeves</a></i></b>
			</div>
			<div class="license">
				Released under <a href="javascript:togglelicense();">MIT OSL License</a>
			</div>
			<div id="m_license" style="display: none">
 			<?php echo nl2br(file_get_contents("../inc/license.txt"))?>
 			</div>
			<div>
				Online Help : see <a
					href="http://wiki.magmi.org" target="wiki">Wiki</a>
				or plugin panels documentation link
			</div>
		</div>
		<div class="info" style="float: right; width: 180px">
			<h3>CPMagento</h3>
			<div class="donate">
				www.CPMagento.com <br /> <a
					href="http://www.cpmagento.com"
					target="_blank"></a>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
togglelicense=function()
{
 $('m_license').toggle();
}
</script>



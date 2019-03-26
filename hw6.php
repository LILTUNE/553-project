
<?php
	$keyword = $category = $Nearby = $distance = $zip_input = $item_id = $zip_flag = "";
	$condition = $shipping = [];
	$json = $geojson = $detail_json = $similar_json = "";
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		if(!empty($_POST["item_id"])){
			//echo "item_id:"+ $_POST["item_id"];
			$item_id = $_POST["item_id"];
			$detail_json = get_detail($item_id);
			file_put_contents("detail.html", $detail_json->{"Item"}->{"Description"});
			$similar_json = get_similar($item_id);
		}
		else{
			$keyword = $_POST["keyword"];
			$category = $_POST["category"];
			if (isset($_POST["Nearby"])){
				$Nearby = $_POST["Nearby"];
			}
			if($zip_input != ""){
				$url .= "&buyerPostalCode=".$zip_input;
			}
			if (isset($_POST["distance"])){
				$distance=$_POST["distance"];
			}
			else{
				$distance=10;
			}
			if (isset($_POST["zip_input"])) {
				$zip_input = $_POST["zip_input"];
				if($zip_input != "" && !validateZipCode($zip_input)){
					$zip_flag = "false";
				}

			}
			if (isset($_POST["condition"])) {
				$condition = $_POST["condition"];
			}
			/*else{
			 	$condition = array("New", "Used", "Unspecified");
			}*/
			if (isset($_POST["shipping"])) {
				$shipping = $_POST["shipping"];
			}
			/*else{
				$shipping = array("FreeShippingOnly", "LocalPickupOnly");
			}*/
			if($zip_flag!="false"){
				$json = get_items($keyword, $category, $condition, $Nearby, $shipping, $distance, $zip_input);
			}

			//echo $json->{"findItemsAdvancedResponse"}[0]->{"ack"}[0];
		}
	}
	/*foreach($_POST as $key => $value) {
		if ($key !== "search") {
			echo $key . " = " . $value . "\n";
		}
	}*/
	function validateZipCode($zipCode) {
		if (preg_match('#[0-9]{5}#', $zipCode))
			return true;
		else return false;
	}
	function get_items($keyword,$category,$condition,$Nearby,$shipping,$distance,$zip_input){
		$filter_num=0;
		$url = "https://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=TongLiu-FirstPHP-PRD-d16e5579d-8138441b&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&paginationInput.entriesPerPage=20";
		$url .= "&keywords=".urlencode($keyword);
		if($Nearby != ""){
			if($zip_input != ""){
				$url .= "&buyerPostalCode=".$zip_input;
			}
			else{
				$url .= "&buyerPostalCode=".$Nearby;
			}
			if(!empty($distance)){
				$url .= "&itemFilter(".$filter_num.").name=Max_Distance&itemFilter(".$filter_num.").value=".$distance;
				++$filter_num;
			}
			else{
				$url .= "&itemFilter(".$filter_num.").name=Max_Distance&itemFilter(".$filter_num.").value=10";
				++$filter_num;
			}
		}
		switch ($category) {
		    case "Art":
		        $url .= "&"."category_id=550";
		        break;
		    case "Baby":
		        $url .= "&"."category_id=2984";
		        break;
		    case "Books":
		        $url .= "&"."category_id=267";
		        break;
		    case "Clothing, Shoes & Accessories":
		        $url .= "&"."category_id=11450";
		        break;
		    case "Computers/Tablets & Networking":
		        $url .= "&"."category_id=58058";
		        break;
		    case "Health & Beauty":
		        $url .= "&"."category_id=26395";
		        break;
		    case "Music":
		        $url .= "&"."category_id=11233";
		        break;
		    case "Video Games & Consoles":
		        $url .= "&"."category_id=1249";
		        break;
		}
		$url .= "&itemFilter(".$filter_num.").name=HideDuplicateItems";
		$url .= "&itemFilter(".$filter_num.").value=true";
		++$filter_num;
		if($condition){
			$url .= "&itemFilter(".$filter_num.").name=Condition";
			foreach ($condition as $con){
				$url .= "&itemFilter(".$filter_num.").value=".$con;
			}
			
		}
		foreach ($shipping as $ship){
			if($ship == "FS"){
				$url .= "&itemFilter(".$filter_num.").name=FreeShippingOnly";
				$url .= "&itemFilter(".$filter_num.").value=true";
				++$filter_num;
			}
			else if($ship == "Local Pickup"){
				$url .= "&itemFilter(".$filter_num.").name=LocalPickupOnly";
				$url .= "&itemFilter(".$filter_num.").value=true";
				++$filter_num;				
			}

			//$url .= "&itemFilter(".$filter_num.").name=".$ship;
			//$url .= "&itemFilter(".$filter_num.").value=true";
			//++$filter_num;
		}
		echo $url;
		$json = json_decode(file_get_contents($url));
		return $json;
	}
	function get_detail($item_id){
		$url="http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=TongLiu-FirstPHP-PRD-d16e5579d-8138441b&siteid=0&version=967&ItemID=".$item_id."&IncludeSelector=Description,Details,ItemSpecifics";
		$detail_json = json_decode(file_get_contents($url));
		return $detail_json;
	}
	function get_similar($item_id){
		$url="http://svcs.ebay.com/MerchandisingService?OPERATION-NAME=getSimilarItems&SERVICE-NAME=MerchandisingService&SERVICE-VERSION=1.1.0&CONSUMER-ID=TongLiu-FirstPHP-PRD-d16e5579d-8138441b&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&itemId=".$item_id."&maxResults=8";
		$similar_json = json_decode(file_get_contents($url));
		return $similar_json;
	}
?>
<html>
	<title>Search Place</title>
	<meta charset="utf-8">
	<meta name="referrer" content="no-referrer">
	<meta author="Tong Liu">
<style type="text/css">
	body{
		text-align: center;
		font-family: Times, serif;
	}

	.header-product {
		font-style: italic;
		font-size: 30px;
		margin-bottom: 9px;
	}
	#formbox{
		border: 2px;
		border-color: rgb(195, 195, 195);
		height: 311px;
		width: 600px;
		margin: auto;
		padding: 10px;
		text-align: left;
		line-height: 30px;
	}
	#distances{
		margin-left:160px;
		margin-top:-30px;
	}
	#distance{
		margin-left: 30px;
	}

	#location_list{
		margin-top: -30px;
		margin-left: 121px;
	}
	.gray{
		pointer-events: none;
        opacity: 0.5;
	}
	#buttons{
		margin-left: 220px;
		margin-top: -32px;  
	}
	#results {
		margin: auto;
		margin-top: 0px;
		text-align: center;
		position: relative;
	}

	table {
		border-collapse: collapse;
	}

	#results table {
		margin: auto;
	}

	#results table tr td {
		border: 1px solid;
		border-color: rgb(195, 195, 195);
	}
	#not_zip_div{
		visibility: hidden;
		text-align:center;
		background-color: rgb(240,240,240);
    	width: 70%;
    	position: relative;
    	margin: auto;
	}

	.result_table{
		width: 1200px;
	}
	.result_img_td{
		width: 80px;
	}

	.arrow{
		width : 50px;
		height: 25px;
		vertical-align: middle;
	}
	.cell_div{
		width: 150px;
		position: relative;
		display: table-cell;
		padding: 10px 20px;
	}
	.detail_header{
		text-align: center;
		font-size: 40px;
	}
	.detail_img{
		width: 300px;
	}
	.similar_img{

	}
	.similar_div{
		width: 800px;
		overflow-x: auto;
		overflow-y: hidden;
		margin: auto; border: 2px solid rgb(182,182,182);
		visibility:hidden;
	}
	.detail_iframe{
		 width: 90%;
		 display: none;
		 position: relative;
		 margin: auto;
		 border: none;
	}
	a {
		text-decoration: none;
		color: black;
	}

	a:hover {
		color: grey;
	}
</style>
<body>
	<div id="formbox">
		<form id="search_form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>" >
			<fieldset>
			<div class="header-product" style="text-align: center">Product Search</div>
			<hr>
			<label for="keyword"><b>Keyword</b></label>
			<input id="keyword" name="keyword" type="text" value="<?php echo $keyword ?>" required="required">
			<br>
			<label for="category"><b>Category</b></label>
			<select name="category" id="category">
				<option value="All Categories" <?php if(isset($_POST["category"]) && "All Categories" == $_POST["category"]){echo "selected";}?>>All Categories</option>
				<option value="Art" <?php if(isset($_POST["category"]) && $_POST["category"]== "Art"){echo "selected";}?> >Art</option>
				<option value="Baby" <?php if("Baby" == $category){echo "selected";}?>>Baby</option>
				<option value="Books" <?php if("Books"== $category){echo "selected";}?>>Books</option>
				<option value="Clothing, Shoes & Accessories" <?php if("Clothing, Shoes & Accessories" == $category){echo "selected";}?>>Clothing, Shoes & Accessories</option>
				<option value="Computers/Tablets & Networking" <?php if("Computers/Tablets & Networking" == $category){echo "selected";}?>>Computers/Tablets & Networking</option>
				<option value="Health & Beauty" <?php if("Health & Beauty" == $category){echo "selected";}?>>Health & Beauty</option>
				<option value="Music" <?php if("Music"== $category){echo "selected";}?>>Music</option>
				<option value="Video Games & Consoles" <?php if("Video Games & Consoles" == $category){echo "selected";}?>>Video Games & Consoles</option>
			</select>
			<br>

			<label for="Condition"><b>Condition</b></label>
			<INPUT type=checkbox name=condition[] value="New" <?php if(isset($_POST["condition"])&&(in_array("New", $_POST["condition"]))){echo "checked";}?>>New
			<INPUT type=checkbox name=condition[] value="Used" <?php if(isset($_POST["condition"])&&(in_array("Used", $_POST["condition"]))){echo "checked";}?>>Used
			<INPUT type=checkbox name=condition[] value="Unspecified" <?php if(isset($_POST["condition"])&&(in_array("Unspecified", $_POST["condition"]))){echo "checked";}?>>Unspecified
			<br>

			<label for="Shipping Options"><b>Shipping Options</b></label>
			<INPUT type=checkbox name=shipping[] value="Local Pickup" <?php if(isset($_POST["shipping"])&&(in_array("Local Pickup", $_POST["shipping"]))){echo "checked";}?>>Local 
			<INPUT type=checkbox name=shipping[] value="FS" <?php if(isset($_POST["shipping"])&&(in_array("FS", $_POST["shipping"]))){echo "checked";}?>>Free Shipping 

			<br>
			<INPUT type=checkbox id="Nearby" name="Nearby" value="Nearby" onclick="change_list()" <?php if(isset($_POST["Nearby"])){echo "checked";}?> ><b>Enable Nearby Search  </b>
			<div id="distances" class="gray">
				<input id="distance" name="distance" type="text" placeholder="10" size="5" value="<?php echo $distance ?>"><b> miles from</b>
				<ul id="location_list" name="location_list">
					<li style="list-style-type:none;">
						<input type="radio" id="here_radio" name="here_radio" value="here" checked="checked" onclick="click_radio1()" <?php if(!empty($_POST["here_radio"])){echo "checked";}?>>
						<label for="here">Here</label>
					</li>
					<li style="list-style-type:none;">
						<input type="radio" id="zip_radio" name="zip_radio" value="user_input" onclick="click_radio2()" <?php if(isset($_POST["zip_radio"])){echo "checked";}?>>
						<input type="text" id="zip_input" name="zip_input" disabled="disabled" placeholder="zip code" required="required" <?php if(isset($_POST["zip_input"])){echo $_POST["zip_input"];}?>>
					</li>
				</ul>
			</div>
			<input id="item_id" name="item_id" style="visibility: hidden"></input>
			<div id="buttons">
				<button id="search" name="search" type="submit" disabled="ture" onclick="check_zip()">Search</button>
				<input id="clear" name="clear" type="button" onclick="clearpage()" value="Clear">
			</div>
			</fieldset>
		</form>
	</div>
	<div id="results"></div>
	<div id="not_zip_div"></div>
</body>

<script type="text/javascript">
	var form = document.getElementById("search_form");
	
	var xhttp = new XMLHttpRequest();
	var geojson = request_Location_Json();
	var search = document.getElementById("search");
	document.getElementById("Nearby").value = geojson["zip"];
	search.disabled = false;
	var results;
	window.addEventListener('onload', change_list());
	function change_list(){
		if(document.getElementById("Nearby").checked){
			var classVal = document.getElementById("distances").getAttribute("class");
			classVal = classVal.replace("gray","");
			document.getElementById("distances").setAttribute("class",classVal);
		}
		else{
			document.getElementById("distances").setAttribute("class","gray");
		}
		var a = '<?php if(isset($_POST["zip_input"])){echo $_POST["zip_input"];}?>';
		if(a != ""){
			click_radio2();
			document.getElementById('zip_input').value = a;	
		}

	}
	if(<?php echo json_encode($json) ?> != ""){
		var ebay_string = JSON.stringify(<?php echo json_encode($json) ?>);//if not stringfy will return an error
		var ebay_json = JSON.parse(ebay_string); 
		if(ebay_json.findItemsAdvancedResponse[0].ack != "Failure"){
			show_result(ebay_json);
		}
		else{
			var not_zip_div = document.getElementById("not_zip_div");
			not_zip_div.innerHTML = "Zipcode is invalid"
			not_zip_div.setAttribute("style","visibility: visible;");

		}
	}
	if(<?php echo json_encode($detail_json) ?> != ""){
			var detail_string = JSON.stringify(<?php echo json_encode($detail_json) ?>);//if not stringfy will return an error
			var detail_json = JSON.parse(detail_string); 
			var similar_json = "";
			if(<?php echo json_encode($similar_json)?> != ""){
				similar_string = JSON.stringify(<?php echo json_encode($similar_json) ?>);
				var similar_json = JSON.parse(similar_string);
			}
			show_detail(detail_json,similar_json);
	}

	// preg_match!!!!!!!!!!!
	
	function check_zip(){
		var zipcode = document.getElementById("zip_input").value;
		if(zipcode!= "" && !is_usZipCode(zipcode)){
			result_div = document.getElementById("results");
		//create header for deatail table
			// var not_zip_div = document.createElement("div");
			// not_zip_div.setAttribute("style", "text-align:center");
			// not_zip_div.innerHTML = "Zipcode is invalid";
			// result_div.appendChild(not_zip_div);
		}
	}

	function is_usZipCode(str)
	{
		regexp = /^[0-9]{5}$/;
		if (regexp.test(str)){
            return true;
        }
        else{
            return false;
        }
    }

	function request_Location_Json(){
		var url = "http://ip-api.com/json/";
		var xmlHttp = new XMLHttpRequest();
		xmlHttp.open( "GET", url, false );
		xmlHttp.send();
		var loca_json = xmlHttp.responseText;     
		var loca_data = JSON.parse(loca_json);
		return loca_data;
	}
	function clearpage() {
		resetForm(form);
		remove_all_child("results");
	}


	function resetForm(form) {
    // clearing inputs
	    var inputs = form.getElementsByTagName('input');
	    for (var i = 0; i<inputs.length; i++) {
	        switch (inputs[i].type) {
	            // case 'hidden':
	            case 'text':
	                inputs[i].value = '';
	                break;
	            case 'radio':
	            	if (inputs[i].name == "here_radio"){
	            		inputs[i].checked = true;
	            	}
	            	else{
	            		inputs[i].checked = false;
	            	}
	            	break;
	            case 'checkbox':
	            	inputs[i].checked = false;
	        }
	    }
	    // clearing selects
	    var selects = form.getElementsByTagName('select');
	    for (var i = 0; i<selects.length; i++)
	        selects[i].selectedIndex = 0;

	    // clearing textarea
	    var text= form.getElementsByTagName('textarea');
	    for (var i = 0; i<text.length; i++)
	         text[i].innerHTML= '';
	    document.getElementById("distances").setAttribute("class","gray");
	    return false;
	}


	function click_radio1(){
		document.getElementById('here_radio').checked=true;
		document.getElementById('zip_radio').checked=false;
		document.getElementById('zip_input').disabled=true;
	}
	function click_radio2(){
		document.getElementById('here_radio').checked=false;
		document.getElementById('zip_radio').checked=true;
		document.getElementById('zip_input').disabled=false;
	}
	function resubmit(element_id){
		document.getElementById("item_id").value=element_id;
		document.getElementById("search_form").submit();
		
	}
	function show_detail(detail_json, similar_json){
		result_div = document.getElementById("results");
		detail_header = document.createElement("div");
		detail_header.className = "detail_header";
		detail_header.innerHTML = "<b>Item Details</b>";
		result_div.appendChild(detail_header);
		//create header for deatail table
		var table = document.createElement("table");
		var Item_obj = detail_json["Item"];
		//store file by php
		// Photo row
		if("PictureURL" in Item_obj){
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Photo</b>";
			var td = tr.insertCell();
			//td.innerHTML= "<img src ="+Item_obj["PictureURL"] + "/>";
			var image = document.createElement("img");
			image.src = Item_obj["PictureURL"];
			image.className = "detail_img";
			td.appendChild(image);
		}
		// Title
		if("Title" in Item_obj){
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Title</b>"
			var td = tr.insertCell();
			td.innerHTML = Item_obj["Title"];
		}
		// SubTitle
		if("Subtitle" in Item_obj){
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Subtitle</b>";
			var td = tr.insertCell();
			td.innerHTML = Item_obj["Subtitle"];
		}
		// Price
		if("CurrentPrice" in Item_obj && "value" in Item_obj["CurrentPrice"] && "CurrencyID" in Item_obj["CurrentPrice"]){// hasProperty
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Price</b>";
			var td = tr.insertCell();
			td.innerHTML = Item_obj["CurrentPrice"]["value"]+Item_obj["CurrentPrice"]["CurrencyID"];
		}
		// Location row
		if("Location" in Item_obj || "postalCode" in Item_obj){
			var location = "";
			if("Location" in Item_obj){
				location = Item_obj["Location"];
			}
			var postalcode = "";
			if("postalcode" in Item_obj){
				postalcode = Item_obj["postalcode"];
			}
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Location</b>";
			var td = tr.insertCell();
			td.innerHTML = location + "," + postalcode;
		}
		// Seller
		if("Seller" in Item_obj && "UserID" in Item_obj["Seller"]){
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Seller</b>";
			var td = tr.insertCell();
			td.innerHTML = Item_obj["Seller"]["UserID"];
		}
		// Return policy
		if("ReturnPilicy" in Item_obj && "ReturnsAccepted" in Item_obj["ReturnPilicy"]){
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Return Policy</b>";
			var td = tr.insertCell();
			td.innerHTML = Item_obj["ReturnPilicy"]["ReturnsAccepted"];
		}
		// Item Specifics(Name)
		if("ItemSpecifics" in Item_obj && "NameValueList" in Item_obj["ItemSpecifics"]){
			var NameValueList = Item_obj["ItemSpecifics"]["NameValueList"];
			var i;
			for(i=0; i<NameValueList.length; i++){
				var tr = table.insertRow();
				var td = tr.insertCell();
				td.innerHTML = "<b>"+NameValueList[i]["Name"]+"</b>";
				var td = tr.insertCell();
				td.innerHTML = NameValueList[i]["Value"];
			}
		}
		result_div.appendChild(table);

		//add arrow after table
		var show_seller = document.createElement("div");
		var p1 = document.createElement("p");
		p1.setAttribute("style", "color: rgb(134,134,134);");
		var content1 = document.createTextNode("click to show seller message");
		//content1.style.color = "rgb(182,182,182)";
		p1.appendChild(content1);
		var arrow1 = document.createElement("img");
		arrow1.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
		arrow1.className = "arrow";
		arrow1.onclick = function () {
			if(arrow1.src == "http://csci571.com/hw/hw6/images/arrow_up.png"){
				arrow1.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
				document.getElementById("detail_iframe").setAttribute("style", "display:none");
				document
			}
			else{
				arrow1.src = "http://csci571.com/hw/hw6/images/arrow_up.png";
				document.getElementById("detail_iframe").setAttribute("style", "display:block");
				onload=resizeIframe(document.getElementById("detail_iframe"));
			}

		};
		var detail_iframe = document.createElement("div");
		detail_iframe.innerHTML = "<iframe id=detail_iframe class=detail_iframe src=detail.html scrolling=no></iframe>";
		show_seller.appendChild(p1);
		show_seller.appendChild(arrow1);
		show_seller.appendChild(detail_iframe)
		result_div.appendChild(show_seller);

		//2nd arrow
		var show_similar = document.createElement("div");
		var p2 = document.createElement("p");
		p2.setAttribute("style", "color: rgb(134,134,134);");
		var content2 = document.createTextNode("click to show similar items");
		//content1.style.color = "rgb(182,182,182)";
		p2.appendChild(content2);
		var arrow2 = document.createElement("img");
		arrow2.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
		arrow2.className = "arrow";
		arrow2.onclick = function () {
			if(arrow2.src == "http://csci571.com/hw/hw6/images/arrow_up.png"){
				arrow2.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
				document.getElementById("similar_div").setAttribute("style", "visibility:hidden");
			}
			else{
				arrow2.src = "http://csci571.com/hw/hw6/images/arrow_up.png";
				document.getElementById("similar_div").setAttribute("style", "visibility:visible");
			}

		};
		show_similar.appendChild(p2);
		show_similar.appendChild(arrow2);
		result_div.appendChild(show_similar);
		var similar_div = document.createElement("div");
		similar_div.className = "similar_div";
		similar_div.id = "similar_div";

		if(similar_json==""){
			similar_div.innerHTML="<b>No Similar Item found.</b>"
		}
		else{
			//similar_div.setAttribute("style", "width: 800px; overflow-x: auto; overflow-y: hidden; margin: auto; border: 2px solid rgb(182,182,182);");
			//similar_div.setAttribute("style", "display: table-cell;");
			var similar_items = similar_json["getSimilarItemsResponse"]["itemRecommendations"]["item"];
			var i;
			var html_text = "";
			for(i=0; i < similar_items.length; i++){
				var cur_sim = similar_items[i];
				html_text += "<div class=cell_div><img class = similar_img src=" + cur_sim["imageURL"] + 'alt="centered image"/>';
				html_text += '<a href=# style= text-align:center; width: 164px; onclick=resubmit(' + cur_sim["itemId"] + ")>" + cur_sim["title"] + "</a>";
				html_text += '<p style="text-align:center;">$' + cur_sim["buyItNowPrice"]["__value__"] + "</p>" + "</div>";
			}
			similar_div.innerHTML=html_text;
		}
		result_div.appendChild(similar_div);




	}
	function remove_all_child(nodename) {
		var node = document.getElementById(nodename);
		while (node && node.firstChild) {
			node.removeChild(node.firstChild);
		}
	}

	function show_result(ebay_json) {
		if (ebay_json == null) {
			return;
		}
		result_div = document.getElementById("results");
		if(ebay_json.findItemsAdvancedResponse[0]==='undefined'){
			//alert('error');
		}
		if (ebay_json.findItemsAdvancedResponse[0].searchResult[0]["@count"]=="0"){
			var not_zip_div = document.getElementById("not_zip_div");
			not_zip_div.innerHTML = "No Records has been found";
			not_zip_div.setAttribute("style","visibility: visible;");
			// var node = document.createElement("div");
			// node.innerHTML = "<b>No Records has been found!<b>";
			// node.id = "no_record";
			// result_div.appendChild(node);

			return;
		}
		item_list = ebay_json.findItemsAdvancedResponse[0].searchResult[0]["item"];

		var table = document.createElement("table");
		table.className = "result_table";
		var th = table.insertRow();
		var thc1 = document.createElement("td");
		var thc2 = document.createElement("td");
		var thc3 = document.createElement("td");
		var thc4 = document.createElement("td");
		var thc5 = document.createElement("td");
		var thc6 = document.createElement("td");
		var thc7 = document.createElement("td");
		thc1.innerHTML = "<b>Index</b>";
		thc2.innerHTML = "<b>Photo</b>";
		thc3.innerHTML = "<b>Name</b>";
		thc4.innerHTML = "<b>Price</b>";
		thc5.innerHTML = "<b>Zip code</b>";
		thc6.innerHTML = "<b>Condition</b>";
		thc7.innerHTML = "<b>Shipping Option</b>";
		th.appendChild(thc1);
		th.appendChild(thc2);
		th.appendChild(thc3);
		th.appendChild(thc4);
		th.appendChild(thc5);
		th.appendChild(thc6);
		th.appendChild(thc7);
		table.appendChild(th);
		for(i=0;i<item_list.length;i++){
			cur_item = item_list[i];
			var tr = table.insertRow();

			//fill Index
			var td = tr.insertCell();
			td.innerHTML = i+1;

			//fill Photo
			var td = tr.insertCell();
			td.className = "result_img_td";
			td.innerHTML= "<img src ="+cur_item["galleryURL"] + "/>";
			/*var image = document.createElement("img");
			image.src = cur_item["galleryURL"];
			td.appendChild(image);*/

			//fill Name
			var td = tr.insertCell();
			if("title" in cur_item){
				td.innerHTML = "<a href=# onclick=resubmit(" + cur_item["itemId"] + ")>" + cur_item["title"] + "</a>";
			}
			else{
				td.innerHTML = "N/A";
			}

			//fill Price
			var td = tr.insertCell();
			td.innerHTML = "$" + cur_item["sellingStatus"][0]["currentPrice"][0]["__value__"];

			//fill Zip code
			var td = tr.insertCell();
			if("postalCode" in cur_item){
				td.innerHTML = cur_item["postalCode"];
			}
			else{
				td.innerHTML = "N/A";
			}
			
			var td = tr.insertCell();
			if("condition" in cur_item && "conditionDisplayName" in cur_item["condition"][0]){
				td.innerHTML = cur_item["condition"][0]["conditionDisplayName"];
			}
			else{
				td.innerHTML = "N/A";
			}

			//fill Shipping Option
			var td = tr.insertCell();
			if("shippingInfo" in cur_item && "shippingServiceCost" in cur_item["shippingInfo"][0] && "__value__" in cur_item["shippingInfo"][0]["shippingServiceCost"]){
				//td.innerHTML = cur_item["shippingInfo"][0]["shippingType"];
				td.innerHTML = "$" + cur_item["shippingInfo"][0]["shippingServiceCost"]["__value__"];
			}
			else{
				td.innerHTML = "N/A"
			}
			table.appendChild(tr);
		}
		result_div.appendChild(table);
	}
	function resizeIframe(obj) {
		obj.style.height = obj.contentWindow.document.body.scrollHeight + 'px';
	}
</script>

</html>


<?php
	$keyword = $category = $Nearby = $distance = $zip_input = $item_id= "";
	$condition = $shipping = [];
	$json = $geojson = $detail_json = "";
	if ($_SERVER["REQUEST_METHOD"] == "POST"){
		//echo "in";
		//echo $_POST["item_id"] + "item id";
		if(!empty($_POST["item_id"])){
			echo "item_id";
			echo $_POST["item_id"];
			$item_id = $_POST["item_id"];
			$detail_json = get_detail($item_id);
		}
		else{
			echo "php post";
			$keyword = $_POST["keyword"];
			$category = $_POST["category"];
			if (isset($_POST["Nearby"])){
				$Nearby = $POST["Nearby"];
			}
			if (isset($_POST["distance"])){
				$distance=$_POST["distance"];
			}
			else{
				$distance=10;
			}
			if (isset($_POST["zip_input"])) {
				$zip_input = $_POST["zip_input"];
			}
			if (isset($_POST["condition"])) {
				$condition = $_POST["condition"];
			}
			else{
				$condition = array("New", "Used", "Unspecified");
			}
			if (isset($_POST["shipping"])) {
				$shipping = $_POST["shipping"];
			}
			else{
				$shipping = array("FreeShippingOnly", "LocalPickupOnly");
			}
			//$geojson = json_decode($_POST["geojson"]);
			$json = get_items($keyword, $category, $condition, $Nearby, $shipping, $distance, $zip_input);
			//echo $json->{"findItemsAdvancedResponse"}[0]->{"ack"}[0];
		}
	}
	/*foreach($_POST as $key => $value) {
		if ($key !== "search") {
			echo $key . " = " . $value . "\n";
		}
	}*/
	function get_items($keyword,$category,$condition,$Nearby,$shipping,$distance,$zip_input){
		$filter_num=0;
		$url = "https://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=TongLiu-FirstPHP-PRD-d16e5579d-8138441b&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&paginationInput.entriesPerPage=20";
		$url .= "&keywords=".urlencode($keyword);
		if($zip_input != ""){
			$url .= "&buyerPostalCode=".$zip_input;
		}
		if($Nearby != ""){
			if(isset($distance)){
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
		$url .= "&itemFilter(".$filter_num.").name=condition";
		foreach ($condition as $con){
			$url .= "&itemFilter(".$filter_num.").value=".$con;
		}
		++$filter_num;
		if (in_array("Free Shipping", $shipping)){
			$url .= "&itemFilter(".$filter_num.").name=FreeShippingOnly&itemFilter(".$filter_num.").value=true";
			++$filter_num;
		}
		if(in_array("Local Pickup", $shipping)){
			$url .= "&itemFilter(".$filter_num.").name=LocalPickupOnly&itemFilter(".$filter_num.").value=true";
			++$filter_num;
		}
		echo $url;
		//$url="https://svcs.ebay.com/services/search/FindingService/v1?OPERATION-NAME=findItemsAdvanced&SERVICE-VERSION=1.0.0&SECURITY-APPNAME=TongLiu-FirstPHP-PRD-d16e5579d-8138441b&RESPONSE-DATA-FORMAT=JSON&REST-PAYLOAD&paginationInput.entriesPerPage=20&keywords=".urlencode($keyword)."";
		$json = json_decode(file_get_contents($url));
		return $json;
	}
	function get_detail($item_id){
		$url="http://open.api.ebay.com/shopping?callname=GetSingleItem&responseencoding=JSON&appid=TongLiu-FirstPHP-PRD-d16e5579d-8138441b&siteid=0&version=967&ItemID=".$item_id."&IncludeSelector=Description,Details,ItemSpecifics";
		echo $url;
		$detail_json = json_decode(file_get_contents($url));
		return $detail_json;
	}
?>
<html>
	<title>Search Place</title>
	<meta charset="utf-8">
	<meta name="referrer" content="no-referrer">
	<meta author="Tong Liu">
<style type="text/css">
	#formbox{
		border: 2px;
		border-color: rgb(195, 195, 195);
		height: 200px;
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
	}
	#results {
		margin: auto;
		margin-top: 135px;
		text-align: center;
		width: 1000px;
	}

	table {
		border-collapse: collapse;
	}

	#results table {
		margin: auto;
	}

	#results table tr td {
		height: 40px;
		border: 1px solid;
		border-color: rgb(195, 195, 195);
	}

	.arrow{
		width : 50px;
		height: 25px;
		vertical-align: middle;
	}
</style>
<body>
	<div id="formbox">
		<form id="search_form" method="POST" action="">
			<fieldset>
			<h2 style="text-align: center"><i>Product Search</i></h2>
			<hr>
			<label for="keyword"><b>Keyword</b></label>
			<input id="keyword" name="keyword" type="text" value="<?php echo $keyword ?>" required="required">
			<br>
			<label for="category"><b>Category</b></label>
			<select name="category" id="category">
				<option value="All Categories" "<?php if("All Categories" == $category){echo "selected";}?>">All Categories</option>
				<option value="Art" "<?php if("Art" == $category){echo "selected";}?>">Art</option>
				<option value="Baby" "<?php if("Baby" == $category){echo "selected";}?>">Baby</option>
				<option value="Books" "<?php if("Books"== $category){echo "selected";}?>">Books</option>
				<option value="Clothing, Shoes & Accessories" "<?php if("Clothing, Shoes & Accessories" == $category){echo "selected";}?>">Clothing, Shoes & Accessories</option>
				<option value="Computers/Tablets & Networking" "<?php if("Computers/Tablets & Networking" == $category){echo "selected";}?>">Computers/Tablets & Networking</option>
				<option value="Health & Beauty" "<?php if("Health & Beauty" == $category){echo "selected";}?>">Health & Beauty</option>
				<option value="Music" "<?php if("Music"== $category){echo "selected";}?>">Music</option>
				<option value="Video Games & Consoles" "<?php if("Video Games & Consoles" == $category){echo "selected";}?>">Video Games & Consoles</option>
			</select>
			<br>

			<label for="Condition"><b>Condition</b></label>
			<INPUT type=checkbox name=condition[] value="New" "<?php if(isset($_POST["condition"])&&(in_array("News", $condition))){echo "checked";}?>">New
			<INPUT type=checkbox name=condition[] value="Used" "<?php if(isset($_POST["condition"])&&(in_array("Used", $condition))){echo "checked";}?>">Used
			<INPUT type=checkbox name=condition[] value="Unspecified""<?php if(isset($_POST["condition"])&&(in_array("Unspecified", $condition))){echo "checked";}?>">Unspecified
			<br>

			<label for="Shipping Options"><b>Shipping Options</b></label>
			<INPUT type=checkbox name=shipping[] value="Local Pickup" "<?php if(isset($_POST["shipping"])&&(in_array("Local Pickup", $condition))){echo "checked";}?>">Local Pickup
			<INPUT type=checkbox name=Shipping[] value="Free Shipping" "<?php if(isset($_POST["shipping"])&&(in_array("Free Shipping", $condition))){echo "checked";}?>">Free Shipping
			<br>
			<INPUT type=checkbox id="Nearby" name="Nearby" value="Nearby" onclick="change_list()"><b>Enable Nearby Search  </b>
			<div id="distances" class="gray">
				<input id="distance" name="distance" type="text" placeholder="10" size="5" ><b> miles from</b>
				<ul id="location_list" name="location_list">
					<li style="list-style-type:none;">
						<input type="radio" id="here_radio" name="here_radio" value="here" checked="checked" onclick="click_radio1()">
						<label for="here">Here</label>
					</li>
					<li style="list-style-type:none;">
						<input type="radio" id="zip_radio" name="zip_radio" value="user_input" onclick="click_radio2()">
						<input type="text" id="zip_input" name="zip_input" disabled="disabled" placeholder="zip code" required="required">
					</li>
				</ul>
			</div>
			<input id="item_id" name="item_id" style="visibility: hidden"></input>
			<div id="buttons">
				<button id="search" name="search" type="submit" disabled="ture">Search</button>
				<input id="clear" name="clear" type="button" onclick="clearpage()" value="Clear">
			</div>
			</fieldset>
		</form>
	</div>
	<div id="results"></div>
</body>

<script type="text/javascript">
	var form = document.getElementById("search_form");
	
	var xhttp = new XMLHttpRequest();
	var geojson = request_Location_Json();
	alert("获取到了地理位置");	
	var search = document.getElementById("search");
	search.disabled = false;
	var results;
	if(<?php echo json_encode($json) ?> != ""){
		var ebay_string = JSON.stringify(<?php echo json_encode($json) ?>);//if not stringfy will return an error
		alert(ebay_string);
		var ebay_json = JSON.parse(ebay_string); 
		alert("获取到了ebay_json");
		show_result(ebay_json);

	}
	if(<?php echo json_encode($detail_json) ?> != ""){
			var detail_string = JSON.stringify(<?php echo json_encode($detail_json) ?>);//if not stringfy will return an error
			alert("detail_json的内容是");
			alert(detail_string);
			var detail_json = JSON.parse(detail_string); 
			alert("获取到了detail_json");
			alert(detail_json)
			show_detail(detail_json);
		}
		else{
			alert("detail_json是空的！");
	}
	//alert(ebay_json.findItemsAdvancedResponse[0].ack[0]);

	form.addEventListener("submit", function(event) {
		alert("addEventListener");
		alert(ebay_json.findItemsAdvancedResponse[0].ack[0]);
		event.preventDefault();
	}, false);

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
		//document.getElementById('keyword').value="";

		form.reset();
		remove_all_child("results");
	}
	function change_list(){
		if(document.getElementById("Nearby").checked){
			var classVal = document.getElementById("distances").getAttribute("class");
			classVal = classVal.replace("gray","");
			document.getElementById("distances").setAttribute("class",classVal);
		}
		else{
			document.getElementById("distances").setAttribute("class","gray");
		}
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
	function show_detail(detail_json){
		alert('show detail');
		if (detail_json == null) {
			alert('detail_json null')
			return;
		}
		alert(detail_json["Item"]);
		result_div = document.getElementById("results");
		//create header for deatail table
		var table = document.createElement("table");
		var th = table.insertRow();
		var thc1 = document.createElement("td");
		var thc2 = document.createElement("td");
		thc1.innerHTML = "<b>HTML Key</b>";
		thc2.innerHTML = "<b>API service response</b>";
		th.appendChild(thc1);
		th.appendChild(thc2);
		table.appendChild(th);
		var Item_obj = detail_json["Item"];
		var keys =["Photo", "Title", "Subtitle", "Price","Location", "Seller", "Return Policy", "NameValueList"];
		// Photo row
		if("PictureURL" in Item_obj){
			var tr = table.insertRow();
			var td = tr.insertCell();
			td.innerHTML = "<b>Photo</b>";
			var td = tr.insertCell();
			var image = document.createElement("img");
			image.src = Item_obj["PictureURL"];
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
		var content1 = document.createTextNode("click to show seller message");
		//content1.style.color = "rgb(182,182,182)";
		p1.appendChild(content1);
		var arrow1 = document.createElement("img");
		arrow1.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
		arrow1.className = "arrow";
		show_seller.appendChild(p1);
		show_seller.appendChild(arrow1);
		result_div.appendChild(show_seller);

		//2nd arrow
		var show_similar = document.createElement("div");
		var p2 = document.createElement("p");
		var content2 = document.createTextNode("click to show similar items");
		//content1.style.color = "rgb(182,182,182)";
		p2.appendChild(content2);
		var arrow2 = document.createElement("img");
		arrow2.src = "http://csci571.com/hw/hw6/images/arrow_down.png";
		arrow2.className = "arrow";
		show_similar.appendChild(p2);
		show_similar.appendChild(arrow2);
		result_div.appendChild(show_similar);

	}
	function show_result(ebay_json) {

		alert("结果展示");
		if (ebay_json == null) {
			alert('result null')
			return;
		}
		result_div = document.getElementById("results");
		/*if (result_div.firstChild) {
			remove_all_child("results");
		}*/
		alert(ebay_json.findItemsAdvancedResponse[0].ack[0]);
		if(ebay_json.findItemsAdvancedResponse[0]==='undefined'){
			alert('error');
		}
		//alert(ebay_json.findItemsAdvancedResponse[0].searchResult[0]["@count"]);
		if (ebay_json.findItemsAdvancedResponse[0].searchResult[0]["@count"]=="0"){
			var node = document.createElement("div");
			node.innerHTML = "<b>No Records has been found!<b>";
			node.id = "no_record";
			result_div.appendChild(node);
			return;
		}
		//alert("4");

		item_list = ebay_json.findItemsAdvancedResponse[0].searchResult[0]["item"];


		var table = document.createElement("table");
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
		// alert("5");
		for(i=0;i<item_list.length;i++){
			cur_item = item_list[i];
			var tr = table.insertRow();

			//fill Index
			var td = tr.insertCell();
			td.innerHTML = i+1;
			//td.innerHTML = "<b>Index</b>";


			//fill Photo
			var td = tr.insertCell();
			var image = document.createElement("img");
			image.src = cur_item["galleryURL"];
			td.appendChild(image);

			//fill Name
			var td = tr.insertCell();
			if("title" in cur_item){
				var a = document.createElement('a');
				var linkText = document.createTextNode(cur_item["title"]);
				a.appendChild(linkText);
				//a.href = cur_item["viewItemURL"];
				a.addEventListener("click", function(){
					resubmit(cur_item["itemId"]);
				});

				td.appendChild(a);
			}
			else{
				td.innerHTML = "N/A";
			}

			//fill Price
			var td = tr.insertCell();
			td.innerHTML = cur_item["sellingStatus"][0]["currentPrice"][0]["__value__"];

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
			if("shippingInfo" in cur_item){
				td.innerHTML = cur_item["shippingInfo"][0]["shippingType"];
			}
			else{
				td.innerHTML = "N/A"
			}

			table.appendChild(tr);
		}
		result_div.appendChild(table);
	}
</script>

</html>

<?php
    session_start();
    if (!$_SESSION['viridian_loggedin'])
        header('Location: ./login/viridian_login.php');
?>
<html lang="en">
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">  
  <meta name="viewport" content="width=device-width, initial-scale=1">

  <link rel="stylesheet" href="assets/bootstrap.css" />
	<link rel="stylesheet" type="text/css" href="assets/bootstrap.min.css">
	<link rel="stylesheet" type="text/css" href="assets/font-awesome.min.css">
	<link rel="stylesheet" type="text/css" href="assets/animate.css">
	<link rel="stylesheet" type="text/css" href="assets/select2.min.css">
	<link rel="stylesheet" type="text/css" href="assets/perfect-scrollbar.css">
	<link rel="stylesheet" type="text/css" href="assets/util.css">
  <link rel="stylesheet" type="text/css" href="assets/main.css">
  <link rel="stylesheet" type="text/css" href="assets/avgrund.css">
	<link rel="stylesheet" type="text/css" href="assets/demo.css">
  
  
  <link rel="stylesheet" href="assets/custom.css" />
</head>
<body>
  <div class="limiter">
    <div class="head-container">
      <input 
        type="text"
        placeholder="Please input patient ID or patient name" 
        class="input-id-name" 
      />
      <a href="viridianOrders.php">Go to pending order list</a>
    </div>
		<div class="container-table100">
      <div class="wrap-table100">
        <div class="table100 ver5 m-b-110">
          <div class="table100-head">
						<table>
							<thead>
								<tr class="row100 head">
									<th class="cell100 column1">Name</th>
									<th class="cell100 column2">Email</th>
                  <th class="cell100 column3">Phone Number</th>
									<th class="cell100 column4">ID</th>
								</tr>
							</thead>
						</table>
					</div>
          <div class="table100-body js-pscroll ps ps--active-y patients-list">
          </div>
        </div>
      </div>
		</div>
  </div>
  <aside id="orders-popup" class="avgrund-popup">
    <div class="orders-wrapper orders-list-wrapper">
      <h2>Orders List</h2>
      <div class="orders-list">
      </div>
    </div>
    <div class="orders-wrapper orders-details-wrapper">
      <h2>Orders Details</h2>
      <div class="order-details">
      </div>
      <br />
      <button class="dialog-buttons" onclick="backToList()">Back to List</button>
      <button class="dialog-buttons" onclick="saveOrder()">Save Order To a File</button>
    </div>
    <br />
    <br />    
    <button onclick="javascript:closeDialog();" class="close-button">X</button>
  </aside>
  <div class="avgrund-cover"></div>
</body>
<script src="assets/jquery-3.0.0.min.js" crossorigin="anonymous"></script>
<script src="assets/bootstrap.min.js" crossorigin="anonymous"></script>
<script src="assets/avgrund.js" crossorigin="anonymous"></script>

<script>
  var patientID;
  var orderID;
  var orders;
  var patients;
  var activeOrder;
  var activePatient;
  var order_url = "http://<?php echo $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']); ?>/res_orders.php";
  
  getAllPatients();

  function openDialog() {
    $('.orders-list-wrapper').show();
    $('.orders-details-wrapper').hide();
    Avgrund.show( "#orders-popup" );
	}
  function closeDialog() {
    Avgrund.hide();
  }
  function backToList() {
    $('.orders-list-wrapper').show();
    $('.orders-details-wrapper').hide();
  }
  
  $('.input-id-name').on('input', function(e) {
    var toFind = e.target.value;
    if (patients) {
  
      const patientData = patients.data.filter(element => element.name.search(toFind) > -1);
      displayPatients(patientData);
    }
  });

  function setPatientID(id) {
    patientID = id;
    orderID = null;
    activeOrder = null;
    getOrders(id);
  }
  function setOrderID(id, index) {
    orderID = id;
    activeOrder = orders.data[index];
    console.log(activeOrder);
    $('.orders-list-wrapper').hide();
    $('.orders-details-wrapper').show();
    $('#dispense_button').hide();    
    
    var htmlContent = '';
    var details = [
      'id', 'customer', 'caregiverdetails', 'cannatax', 'cashpaid', 'creditpaid',
      'debitpaid', 'discountotal', 'hasbeenpaid', 'ordertotal', 'regulartax', 'totaltax', 'transaction', 'note', 
    ];

    htmlContent +='<div class="order-item-record"><span>Customer Name:</span><span>'+activeOrder.customerdetails.name+'</span></div>';
    htmlContent +='<div class="order-item-record"><span>Date Created:</span><span>'+getFormattedDate(new Date(activeOrder.DateCreated))+'</span></div>';
    htmlContent +='<div class="order-item-record"><span>Payment Date:</span><span>'+getFormattedDate(new Date(activeOrder.Payment_Date))+'</span></div>';

    details.forEach(detail => {
      htmlContent +='<div class="order-item-record"><span>'+detail+'</span><span>'+activeOrder[detail]+'</span></div>';
    })


    htmlContent += "<br />"
    activeOrder.LineItems.forEach((element, index) => {
      htmlContent +='<div class="order-item-record"><span>Line Item:</span><span>'+parseInt(index+1)+'</span></div>';
      htmlContent +='<div class="order-item-record">------------------------------</div>';
      var details = [
        'ProductID', 'Name', 'AmountPaid', 'Batches', 'CannaLineTax', 'CannaTaxRate',
        'CannabisProductID', 'DiscountAmount', 'LineTax', 'LineTotal', 'Notes', 'OrderID', 'TaxRate', 'id', 
        'cbd', 'cbn', 'packageweight', 'price', 'thc', 'type', 'wastate_id'
      ];
      details.forEach(detail => {
        htmlContent +='<div class="order-item-record"><span>'+detail+'</span><span>'+element[detail]+'</span></div>';
      })
     
      element.BatchOrder.forEach((batchOrder) => {
        htmlContent +='<div class="order-item-record"><span>Batch Order</span><span></span></div>';
        htmlContent +='<div class="order-item-record"><span>batchnumber:</span><span>'+batchOrder.batchnumber+'</span></div>';
        htmlContent +='<div class="order-item-record"><span>orderquantity:</span><span>'+batchOrder.orderquantity+'</span></div>';
      });
      htmlContent +='<div class="order-item-record">------------------------------</div>';
    })
    $('.order-details').html(htmlContent);
  }

  function getOrders(patientID) {
    $.get(order_url+'?customerID='+patientID, function( data ) {
      var jsonData1 = JSON.parse(data);
      orders = jsonData1;
      var htmlContent = '';
      orders.data.forEach((element, index) => {
        $.ajax({
          method: 'POST',
          url: "getStatus.php",
          data: { orderID: element.id }
        }).done(function(msg) {
          if (msg == 'success') {
            htmlContent +='<div class="single-order" onClick="setOrderID('+element.id+', '+index+')">';
            htmlContent +='<img src="assets/product-default.jpg" class="product-img" alt="product">';
            
            htmlContent +='<span class="order-caption">'+element.LineItems[0]['Name']+' ('+element.LineItems.length+' items) '+'</span>';
            htmlContent +='</div>';
            $( ".orders-list" ).html(htmlContent);
          }
        });

      });
      $( ".orders-list" ).html(htmlContent);
      openDialog();
    }, "json");
  }


  function displayPatients(p_data) {
      var htmlContent = '<table><tbody>';

      p_data.forEach(element => {
        if (element.ispatient) {
          htmlContent +='<tr class="row100 body" onClick="setPatientID('+element.id+')">';
          htmlContent +='<td class="cell100 column1">'+element.name+'</td>';
          htmlContent +='<td class="cell100 column2">'+element.email+'</td>';
          htmlContent +='<td class="cell100 column3">'+element.phone+'</td>';
          htmlContent +='<td class="cell100 column4">'+element.id+'</td>';

          htmlContent +='</tr>';
        }
      });
      htmlContent += '</tbody></table><div class="ps__rail-x" style="left: 0px; bottom: -603px;"><div class="ps__thumb-x" tabindex="0" style="left: 0px; width: 0px;"></div></div><div class="ps__rail-y" style="top: 603px; height: 585px; right: 0px;"><div class="ps__thumb-y" tabindex="0" style="top: 297px; height: 288px;"></div></div>';
      
      $( ".patients-list" ).html(htmlContent);
  }
  function getAllPatients() {
    $.get( "res_patientids.php", function( data ) {
      var jsonData1 = JSON.parse(data);
      var jsonData2 = jsonData1;
      patients = jsonData1;
      displayPatients(patients.data);
      console.log(patients.data);
      console.log( "Load was performed." );
    }, "json");
  };


  function getFormattedDate(date) {
     var year = date.getFullYear();
     var month = (1 + date.getMonth()).toString();
     month = month.length > 1 ? month : '0' + month;
     var day = date.getDate().toString();
     day = day.length > 1 ? day : '0' + day;
     return year + '-' + month + '-' + day;
  }
  function saveOrder() {
    if (activeOrder) {
      $.post("http://<?php echo $_SERVER['SERVER_NAME'] . dirname($_SERVER['PHP_SELF']); ?>/save_file.php", { order: activeOrder })
        .done(function(){
          alert('Successfully saved to a file');
          $('#dispense_button').show();
      });
    }
  }
</script>
	<script src="assets/popper.js.download"></script>
	<script src="assets/select2.min.js.download"></script>
  <script src="assets/perfect-scrollbar.min.js.download"></script>
  <script>
		$('.js-pscroll').each(function(){
			var ps = new PerfectScrollbar(this);

			$(window).on('resize', function(){
				ps.update();
			})
		});
  </script>
  <script async="" src="assets/js"></script>  
  <script src="assets/main.js.download"></script>
</html>
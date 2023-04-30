<?php include 'includes/session.php'; ?>
<?php include 'includes/header.php'; ?>
<body class="hold-transition skin-blue layout-top-nav">
<div class="wrapper">

	<?php include 'includes/navbar.php'; ?>
	 
	  <div class="content-wrapper">
	    <div class="container">
	      <!-- Main content -->
	      <section class="content">
	        <div class="row">
	        	<div class="col-sm-12">
	        		<h1 class="page-header">YOUR CART</h1>
	        		<div class="box box-solid">
	        			<div class="box-body">
							<table class="table table-bordered">
								<thead>
									<th></th>
									<th>Photo</th>
									<th>Name</th>
									<th>Price</th>
									<th width="20%">Quantity</th>
									<th>Subtotal</th>
								</thead>
								<tbody id="tbody"></tbody>
							</table>
	        			</div>
	        		</div>
	        		<?php if(isset($_SESSION['user'])) : ?>
						<button type="button" class="btn btn-info" data-toggle="modal" data-target="#billingModal">Checkout</button>
						<div id="billingModal" class="modal fade" role="dialog">
							<div class="modal-dialog">
								<div class="modal-content">
									<div class="modal-header">
										<button type="button" class="close" data-dismiss="modal">&times;</button>
										<h4 class="modal-title">Billing Address</h4>
									</div>
									<div class="modal-body">
										<form id="billingForm">
											<div class="form-group">
												<label for="address">Address</label>
												<input type="text" name="address" class="form-control" autocomplete="off" placeholder="Address">
											</div>
											<div class="form-group">
												<label for="city">Town/City</label>
												<input type="text" name="city" class="form-control" autocomplete="off" placeholder="Town/City">
											</div>
											<div class="form-group">
												<label for="state">State</label>
												<input type="text" name="state" class="form-control" autocomplete="off" placeholder="State">
											</div>
											<div class="form-group">
												<label for="pincode">Pin Code</label>
												<input type="text" name="pincode" class="form-control" autocomplete="off" placeholder="Pin Code">
											</div>
											<div class="form-group">
												<button type="submit" class="btn btn-primary" id="billingFormBtn">Pay Now</button>
											</div>
										</form>
									</div>									
								</div>
							</div>
						</div>
					<?php else : ?>
						<h4>You need to <a href='login.php'>Login</a> to checkout.</h4>
	        		<?php endif; ?>
	        	</div>
	        </div>
	      </section>	     
	    </div>
	  </div>
  	<?php $pdo->close(); ?>
  	<?php include 'includes/footer.php'; ?>
</div>

<?php include 'includes/scripts.php'; ?>
<script>
var total = 0;
$(function(){
	$(document).on('click', '.cart_delete', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		$.ajax({
			type: 'POST',
			url: 'cart_delete.php',
			data: {id:id},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();	
					getTotal();				
				}
			}
		});
	});

	$(document).on('click', '.minus', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		if(qty>1){
			qty--;
		}
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	$(document).on('click', '.add', function(e){
		e.preventDefault();
		var id = $(this).data('id');
		var qty = $('#qty_'+id).val();
		qty++;
		$('#qty_'+id).val(qty);
		$.ajax({
			type: 'POST',
			url: 'cart_update.php',
			data: {
				id: id,
				qty: qty,
			},
			dataType: 'json',
			success: function(response){
				if(!response.error){
					getDetails();
					getCart();
					getTotal();
				}
			}
		});
	});

	getDetails();
	getTotal();

	$(document).on('submit', '#billingForm', function(){
		$('#billingFormBtn').attr('disabled', true);
		$('#billingFormBtn').text('Loading..');

		$.post('sales.php', $(this).serialize(), function(res){
			var data = JSON.parse(res);
			if(data['status'] === 'error') {
				var errors = '';
				$.each(data['error'], function(i, v){
					errors += v + "\r\n";
				});
				alert(errors);
				$('#billingFormBtn').attr('disabled', false);
				$('#billingFormBtn').text('Pay Now');
			}
			if(data['status'] === 'success') {
				alert(data['message']);
				window.location.href = 'profile.php';
			}
		});
		return false;
	});

});

function getDetails(){
	$.ajax({
		type: 'POST',
		url: 'cart_details.php',
		dataType: 'json',
		success: function(response){
			$('#tbody').html(response);
			getCart();
		}
	});
}

function getTotal(){
	$.ajax({
		type: 'POST',
		url: 'cart_total.php',
		dataType: 'json',
		success:function(response){
			if(response > 0 ) {
				$('[data-target="#billingModal"]').show();
			} else {
				$('[data-target="#billingModal"]').hide();
			}
		}
	});
}
</script>
</body>
</html>
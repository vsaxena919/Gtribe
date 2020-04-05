jQuery(function($){
	$(document).on('click', '.cx-btn-add', function(e) {
		var clone = $(this).closest('.cx-site-row').clone()
		$('input[type=url]',clone).val('')
		$(this).parent().after(clone)
		$('.cx-btn-remove').attr('disabled',false)
	})
	if( $('.cx-btn-remove').length <= 1 ) $('.cx-btn-remove').attr('disabled',true)
	$(document).on('click', '.cx-btn-remove', function(e) {
		$(this).parent().remove()
		if( $('.cx-btn-remove').length <= 1 ) $('.cx-btn-remove').attr('disabled',true)
	})
	$(".check-all").change(function () {
		var par = $(this).parent().parent()
		var chk = $('.check-this', par)
	    chk.prop('checked',this.checked);
	});
	$('.check-this').change(function () {
		var par = $(this).parent().parent()
		var chk_all = $(".check-all",par)
		var chk = $('.check-this', par)
		if ($('.check-this:checked', par).length == chk.length){
			chk_all.prop('checked',true);
		}
		else {
			chk_all.prop('checked',false);
		}
	});
	$('.see-pro').click(function(e){
		e.preventDefault()
		$('#cx-nav-label-share-logins_upgrade').click()
	})
	$('.cx-free').click(function(e){
		alert('You need to upgrade to pro to add more remote sites!')
		$('#share-logins_upgrade-tab').click()
		return false;
	})
	$('.cx-migrat-nav-tab').click(function(e){
		e.preventDefault()
		var $this = $(this)

		$('#cx-message').hide()

		$('.cx-migrat-nav-tab').removeClass('nav-tab-active')
		$this.addClass('nav-tab-active')

		var target = $this.attr('href')
		$('.group').hide()
		$(target).show()
	})
	$('#export-users-lite, #import-users-lite').submit(function(e){
		e.preventDefault()
		$('#cx-message').text('Pro Feature').css('border-color', '#f00')
	})
	$('.cx-help-heading').click(function(e){
		var $this = $(this)
		var target = $this.data('target')
		$('.cx-help-text:not('+target+')').slideUp()
		if($(target).is(':hidden')){
			$(target).slideDown()
		}
		else {
			$(target).slideUp()
		}
	})
	$(document).on('click', '.cx-btn-validate', function(e){
		var $this = $(this)
		var $par = $this.parent()
		var $remote_site = $this.data('remote_site')
		if($remote_site=='')return;
		$this.text('Validating').attr('disabled',true)
		$.ajax({
			url: ajaxurl,
			data: {'action':'cx-validate', 'remote_site':$remote_site},
			type: 'POST',
			success: function(ret){
				$('#cx-validate-wrap').fadeIn()
				$('#cx-report-view').html(ret)
				$this.text('Validate').attr('disabled',false)
				console.log(ret)
			},
			error: function(ret) {
				$this.text('Validate').attr('disabled',false)
				console.log(ret)
			}
		})
	})
	$('.cx-report-close').click(function(e){
		$('#cx-validate-wrap').fadeOut()
	})
})
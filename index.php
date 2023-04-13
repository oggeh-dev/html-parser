<?php
	require_once('oggeh.client.php');
	//OGGEH::configure('rewrite', true); // uncomment to enable rewrite settings (rename htaccess.txt to .htaccess)
	OGGEH::configure('domain', 'domain.ltd');
	OGGEH::configure('api_key', '57ff136718d176aae148c8ce9aaf6817');
	// Enable development environment
	OGGEH::configure('sandbox_key', '39e55bb297b9943cfdab5d77cbf4f374');
	OGGEH::configure('sandbox', true);
	
	OGGEH::configure('i18n', array(
		'home'=>array(
			'en'=>'Home',
			'ar'=>'الرئيسية'
		),
		'menu'=>array(
			'en'=>'Menu',
			'ar'=>'القائمة'
		),
		'album'=>array(
			'en'=>'Gallery',
			'ar'=>'معرض الصور'
		),
		'all-news'=>array(
			'en'=>'News',
			'ar'=>'الأخبار'
		),
		'learn-more'=>array(
			'en'=>'Learn more',
			'ar'=>'اقرأ المزيد'
		),
		'highlights'=>array(
			'en'=>'Highlights',
			'ar'=>'مقتطفات'
		),
		'latest-news'=>array(
			'en'=>'Latest News',
			'ar'=>'آخر الأخبار'
		),
		'get-in-touch'=>array(
			'en'=>'Get in Touch',
			'ar'=>'تواصل معنا'
		),
		'contact-us'=>array(
			'en'=>'Contact us',
			'ar'=>'اتصل بنا'
		),
		'request-quote'=>array(
			'en'=>'Request a Quote',
			'ar'=>'استعلام عن الأسعار'
		),
		'continue-reading'=>array(
			'en'=>'Continue Reading',
			'ar'=>'اقرأ المزيد'
		),
		'showing-results-for'=>array(
			'en'=>'Showing results for',
			'ar'=>'عرض نتائج البحث عن'
		),
		'search-not-found'=>array(
			'en'=>'Not resuts found',
			'ar'=>'لا توجد نتائج'
		),
		'page'=>array(
			'en'=>'page',
			'ar'=>'صفحة'
		),
		'news'=>array(
			'en'=>'news',
			'ar'=>'خبر'
		),
		'your-name'=>array(
			'en'=>'Your Name',
			'ar'=>'الاسم'
		),
		'email-address'=>array(
			'en'=>'Email',
			'ar'=>'البريد الالكتروني'
		),
		'message'=>array(
			'en'=>'Message',
			'ar'=>'الرسالة'
		),
		'send-inquiry'=>array(
			'en'=>'Send',
			'ar'=>'ارسل'
		),
		'submit'=>array(
			'en'=>'Submit',
			'ar'=>'تسجيل'
		),
		'reset'=>array(
			'en'=>'Reset',
			'ar'=>'إفراغ'
		),
		'form-success'=>array(
			'en'=>'Thanks for your enquiry, we\'ll be in touch shortly.',
			'ar'=>'شكرا جزيلا، سيتم الاتصال بك في أقرب وقت.'
		),
		'form-error'=>array(
			'en'=>'Please fill in all fields correctly.',
			'ar'=>'رجاء ملء جميع الحقول بشكل صحيح.'
		),
	  'category'=>array(
	    'en'=>'Category',
	    'ar'=>'التصنيف'
	  ),
	  'client'=>array(
	    'en'=>'Client',
	    'ar'=>'العميل'
	  ),
	  'page-not-found'=>array(
	    'en'=>'The page you were looking for doesn\'t appear to exist',
	    'ar'=>'الصفحة التي تبحث عنها غير موجودة'
	  ),
	  'back-to-home'=>array(
	    'en'=>'Go back to home page',
	    'ar'=>'العودة الى الصفحة الرئيسية'
	  ),
	  'under-maintenance'=>array(
	    'en'=>'Under Maintenance',
	    'ar'=>'تحت الانشاء'
	  ),
	  'under-maintenance-message'=>array(
	    'en'=>'We\'re updating our content, come back later!',
	    'ar'=>'نقوم بتحديث المحتوى، عد لزيارتنا لاقحا!'
	  )
	));
	$oggeh = new OGGEH();
	echo $oggeh->display();
	/**
	 * NOTES
	 * this script must be called only once per page request
	 * make sure to include a valid favicon
	 * avoid using blank or invalid source path in img tags
	 */
	//error_log('request made on: '.date('H:i:s d/m/Y'));
?>

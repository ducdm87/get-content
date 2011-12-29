<?php
// duyet category. Next tung trang
// link run: http://domain.com/index.php?option=com_getnewsvov&task=getnews&host=en.vietnamnet
// start auto_increment	:	120.110.012
// STATE: FINISH
/**
 * *	thoi gian cho moi lan chay lay bai
 */
$time_exp	=	10;
/**
 *		so luong bai can lay cho moi lan
 */
//$numbercontent	=	30;
/**
 * - true: lấy lại bài viết
 * - false: bỏ qua bài viết đã lấy
 */
$get_existing	= false;
/**
 * - true: lấy nhiều category. sử dụng cho lần chạy đầu tiên. lấy vét cạn
 * - false: chỉ lấy bài viết mới của category chạy sớm nhất. khi đặt cronjob
 */
$get_multicat		= true;
/**
 * : 	đường dẫn đến thư mục ảnh
 */
$path_image		=	'images/vne20';
/**
 * địa chỉ tới nơi chứa ảnhvd
 */
$link_image		=	'/images/vne20';
$section_id		=	1;
$catid			=	1;
/**
 * 
 */
$SiteID 		=	'vne20';
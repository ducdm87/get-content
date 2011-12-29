<?php
// giam id
// link run: http://domain.com/index.php?option=com_getnewsvov&task=getnews&host=en.baomoi
// start auto_increment	:	240.213.110

/**
 * *	thoi gian cho moi lan chay lay bai
 */
$time_exp	=	10;
/**
 *		so luong bai can lay cho moi lan
 */
$numbercontent	=	20;
/**
 * - true: lấy lại bài viết
 * - false: bỏ qua bài viết đã lấy
 */
$get_existing	= false;
/**
 * - true: lấy bài viết cũ. sử dụng cho lần chạy đầu tiên. lấy vét cạn
 * - false: chỉ lấy bài viết mới
 */
$get_old		= true;
/**
 * : 	đường dẫn đến thư mục ảnh
 */
$path_image		=	'images/bm15';
/**
 * địa chỉ tới nơi chứa ảnhvd
 */
$link_image		=	'/images/bm15';
$section_id		=	1;
$catid			=	1;
/**
 * 
 */
$SiteID 		=	'bm15';
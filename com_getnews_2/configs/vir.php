<?php
// duyet theo category. lay danh sach bai viec trong moi bai viet cuoi cung
// link run: http://domain.com/index.php?option=com_getnews_2&task=getnews&host=vir
// start auto_increment	:	110.201.110
// STATE: FINISH
/**
 * *	thoi gian cho moi lan chay lay bai
 */
$time_exp	=	30;
/**
 *		so luong bai can lay cho moi lan
 */
$numbercontent	=	30;
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
$path_image		=	'images/vr305';
/**
 * địa chỉ tới nơi chứa ảnhvd
 */
$link_image		=	'/images/vr305';
/**
 * 
 */
$SiteID 		=	'vr305';
<?php
      if ($task=='takenew') 
        {
            $row->fulltext = tidy_html_text ($row->fulltext,$row->title_alias);
            $dom = new domDocument;
            $dom->preserveWhiteSpace = TRUE; 
            $dom->loadHTML($row->fulltext);

            ////////////load document
            $text_items2 = $dom->getElementsByTagName('a') ;
            if ($text_items2) {
                $itemnn=0;
                if ($row->id<>0) {
                    $timeid=$row->id;    
                } else {
                    $timeid=time();                           
                }           

                foreach($text_items2 as $text_item2)  
                {
                    $itemnn++;         
                    $external_image_url0=$text_item2->getAttribute('href');
                    $external_image_url = str_replace('https://','http://',$external_image_url0);  
                    
                    if ($external_image_url<>'' && strpos($external_image_url,'vietbao.vn/')===FALSE && strpos($external_image_url,'http://')!==FALSE) {
                        //                    $referer_path=preg_replace('/(\/.*?)$/','',$external_image_url);
                        $external_image_url = trim($external_image_url);
                        $referer_path = preg_replace("/^(http:\/\/)*(www.)*/is", "",$external_image_url);
                        $domain_link = preg_replace("/\/.*$/is" , "" ,$referer_path); 
                        $referer_path = 'http://'.$domain_link;                         
                        //echo $referer_path,'     -     ';
                        //$referer_path ='http://24h.com.vn';
                        $image_filename=take_file_name($external_image_url);
                        $image_type0  = strtolower(substr($image_filename,-4));                       
                        $image_type02 = strtolower(substr($image_filename,-5));

                        $document_type_list=array('.csv','.dot','.dotx','.doc','.docx','.odp','.ods','.odt','.pot','.ppt','.pps','.potx','.pptx','.ppsx','.rtf','.pdf','.txt','.xls','.xlsx','.xlt','.xltx','.xps','.wps','.flv','.rar','.zip','.wmf','.emf');
                        $image_type_list=array ('.gif', '.jpeg', '.png', '.swf', '.psd', '.bmp','.tiff',  '.jpc', '.jp2', '.jpf', '.jb2', '.swc','.aiff', '.wbmp', '.xbm');                                         

                        if ( in_array($image_type0,$document_type_list) || in_array($image_type02,$document_type_list) || in_array($image_type0,$image_type_list) || in_array($image_type02,$image_type_list)) 
                        {                                
                            if ($image_filename) {
                                $image_filename=str_replace(' ','-',$timeid.'-'.$itemnn.'-'.$image_filename);
                            } else { 
                                $image_filename=$timeid.'-'. $itemnn;                    
                            }                                
                            //            $path_save='/var/www/html/wse/images/wii/avatar/';
                            $path_save='D:\\wamp\\www\\vb2\\images\\vn888\\data\\201105\\';
                            $path_url2='../../images/vn888/data/201105/';                    
                            $path_url='images/vn888/data/201105/';                         
                            $url_to_download=str_replace(' ','%20',$external_image_url); 
                            $fp = fopen ($path_save.$image_filename, 'w+');//This is the file where we save the information
                            $ch = curl_init($url_to_download);//Here is the file we are downloading

                            $curl_options_download = array(
                            CURLOPT_FILE  => $fp,
                            CURLOPT_FOLLOWLOCATION => true,     // follow redirects
                            CURLOPT_USERAGENT      => "MozillaMozilla/5.0 (Windows; U; Windows NT 6.0; en-US; rv:1.9.0.11) Gecko/2009060215 Firefox/3.0.9", // who am i
                            CURLOPT_AUTOREFERER    => true,     // set referer on redirect
                            CURLOPT_REFERER    => "$referer_path",
                            CURLOPT_CONNECTTIMEOUT => 900,      // timeout on connect
                            CURLOPT_TIMEOUT        => 900,      // timeout on response
                            CURLOPT_MAXREDIRS      => 10,       // stop after 10 redirects
                            );    

                            curl_setopt_array( $ch, $curl_options_download);   
                            curl_exec($ch);
                            $downloadInfo=curl_getinfo($ch); // print_r($downloadInfo);
                            //                       print_r($downloadInfo);
                            curl_close($ch);
                            fclose($fp);

                            if ( in_array(image_type0,$image_type_list) || in_array(image_type02,$image_type_list)) 
                            {                            

                                list($width, $height, $type, $attr) = getimagesize($path_save.$image_filename);                 
                                if ($type) {
                                    $image_type=image_type_to_extension($type);            
                                    if ($downloadInfo['http_code']==200) {
                                        $new_image_url = $path_url.$image_filename;
                                        /*                                       $text_item_new=$text_item2;
                                        $text_item_new->setAttribute('src',$new_image_url);
                                        $text_item_new->setAttribute('onclick','return showImage(this.src)');
                                        */                                      
                                        $row->fulltext=str_replace($external_image_url,$new_image_url,$row->fulltext);   
                                    }  
                                } else {
                                    unlink ($path_save.$image_filename);
                                }
                            } else 
                            {

                                if ($downloadInfo['http_code']==200) {
                                    $new_image_url = $path_url.$image_filename;
                                    $text_item_new=$text_item2;
                                    $text_item_new->setAttribute('src',$new_image_url);
                                    $text_item_new->setAttribute('onclick','return showImage(this.src)');
                                    $external_image_url =str_replace('?','\?',$external_image_url); 
                                    $row->fulltext=str_replace($external_image_url,$new_image_url,$row->fulltext); 
                                }                                
                            }                                                 

                        } else {  // loai bo link tu va toi source

                            $ignore_domain_list = array('vietnamnet.vn','vnexpress.net','vnmedia.vn','tienphong.vn','tuoitre.vn','tuoitre.com.vn','thanhnien.vn','thanhnien.com.vn','24h.vn',
                            'dantri.com.vn','sgtt.com.vn','nld.com.vn','hanoimoi.com.vn','vtc.vn','baodatviet.vn','bee.net.vn','nongnghiep.vn','vovnews.vn','nguoihanoi.com.vn','danviet.vn','congluan.vn','chinhphu.vn','cafef.vn','vietstock.vn','stockbiz.vn','vneconomy.vn','nongnghiep.vn','ktdt.com.vn','tinnhanhchungkhoan.vn','doanhnhan360.com','baocongthuong.com.vn','dddn.com.vn','tamnhin.net','vovnews.vn','ictnews.vn','khoahocphattrien.com.vn','xahoithongtin.com.vn','baovanhoa.com.vn','gdtd.vn','24h.com.vn','zing.vn','thethaovanhoa.vn','tintuconline.com.vn','2sao.vietnamnet.vn','vtv.vn','xzone.vn','vzone.com.vn','kenh14.vn','megafun.vn','suckhoedoisong.vn','dulichvietnam.com.vn','xinhxinh.com.vn','bongda24h.vn','baobongda.com.vn','gamek.vn','vtc.vn','cand.com.vn','giadinh.net.vn','anninhthudo.vn','phapluattp.vn','doisongphapluat.com.vn','afamily.vn','anninhthudo.vn','nguoicaotuoi.org.vn','antg.cand.com.vn','phunuonline.com.vn','eva.vn','muctim.com.vn','autopro.com.vn','baonghean.vn','baolangson.vn','baodanang.vn','baodientusonla.com.vn','baotuyenquang.com.vn','baohagiang.vn','baoquangninh.com.vn','baogialai.com.vn','baohaiphong.com.vn','baobinhduong.org.vn','baocamau.com.vn','kontum.gov.vn','dalat.gov.vn');
                            if ( in_array($domain_link,$ignore_domain_list)) {                       
                                //$anchor_text = $text_item2->nodeValue; //
                                //                                $link_tag = getNodeInnerHTML($text_item2);
                                //                                $row->fulltext=str_replace($link_tag,$anchor_text,$row->fulltext); 
                                //                                $row->fulltext= preg_replace('#<a .*? >'.$anchor_text.'([^<]*)</a>#i',$anchor_text,$row->fulltext); 
                                $external_image_url =str_replace('?','\?',$external_image_url);                                
                                $row->fulltext= preg_replace('#<a .*?href.*?'.$external_image_url.'.*?>([^<]*)</a>#i','$1',$row->fulltext);                //                                                                             
                                //                                print_r($link_tag); die();                                                            
                            }                             


                        }

                    }        
                }
            }
        }

?>

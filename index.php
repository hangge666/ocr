<?php 
/**
 * 本例是调用百度接口的小例子
 */
        if(empty($_FILES['file']['tmp_name'])){ //文件不存在的话,则重新上传
          
          exit('<title>杭哥扫描仪</title><h2 align="center">性感网站，在线扫描</h2><form  enctype="multipart/form-data" method="post"><input type="file" name="file"/><button type="submit">提交</button></form>');
          if (!empty($_FILES['file'])) {        //完善用户提示
            switch ($_FILES['file']['error']) {
                case 1:
                  echo "上传的文件超过了系统配置限制的大小"; //UPLOAD_ERR_INI_SIZE 其值为 1，上传的文件超过了 php.ini 中 upload_max_filesize 选项限制的值。
                break;
                case 2:
                  echo "文件的大小超过了HTML选项指定的值"; 
                break;
                case 3:
                  echo "文件只有部分被上传";
                break;
                case 4:
                  echo "没有文件被上传";
                break;
                case 6:
                  echo "找不到临时文件夹";
                break;
                case 7:
                  echo "文件写入失败";
                break;
              default:
                echo "出现了无法意料的异常";
                break;
            }
          }
        }

       key 和 secret，自行申请
       $key='';
       $Secret='';

           $file=$_FILES['file']['tmp_name']; //接受表单
           $txt_name="access.txt"; //文件名称
           $access_file = fopen($txt_name, "w+");  //读写，若无新创建
           $expires_time=fgets($access_file); //读取第一行 超时时间
           $access_token=fgets($access_file); //读取第二行
           if (empty($expires_time)||$expires_time<time()) { //文件读取的为空，或者
                $url = 'https://aip.baidubce.com/oauth/2.0/token'; //百度获取acess_token的网址
                /* url参数配置 */
                $post_data['grant_type']       = 'client_credentials';
                $post_data['client_id']      =$key;
                $post_data['client_secret'] = $Secret;
                $o = "";
                foreach ( $post_data as $k => $v ) 
                {
                    $o.= "$k=" . urlencode( $v ). "&" ;
                }
                $post_data = substr($o,0,-1);

                $res = json_decode(request_post($url, $post_data),true);//发送请求

                /* 将access_token和超时时间存入access.txt文件，毕竟有效期一个月 */
                $new_access_token=$res['access_token'];
                $new_expires_time=time()+$res['expires_in'];
                $expires_time=$new_expires_time;
                $access_token=$new_access_token;
                fwrite($access_file, $new_expires_time."\r\n".$new_access_token);
           }

           fclose($access_file); 
           /**按照文档对文件进行base64转码，
           注意有些地方不能urlencode ，
           不然接口会告诉你图片格式异常，
           文档居然不提醒一下，我去*/
           $img_data=file_get_contents($file);
           $data=base64_encode($img_data);
           $data=['image'=>$data]; 
           $url='https://aip.baidubce.com/rest/2.0/ocr/v1/general_basic?access_token='.$access_token;
           /* 按照文档进行请求方式配置 */           
           $ch=curl_init();   
           $headerArray =array("Content-type:application/x-www-form-urlencoded"); //请求头配置
           curl_setopt($ch, CURLOPT_HEADER, $headerArray);
           curl_setopt($ch, CURLOPT_URL, $url);  //url偶尔u照顾
           curl_setopt($ch, CURLOPT_POST, 1);   //采用post
           curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //不验证证书下同
           curl_setopt($ch, CURLOPT_POSTFIELDS, is_array($data) ? http_build_query($data) : $data);
           $result = json_decode(curl_exec($ch),true);//运行curl

           echo "<script>var cxt=document.getElementsByTagName('body')[0].innerHTML;document.getElementsByTagName('body')[0].innerHTML=cxt</script>";
           curl_close($ch);
           
   
    function request_post($url = '', $param = '') {
        if (empty($url) || empty($param)) {
            throw new Exception("", 1);
        }
        
        $postUrl = $url;
        $curlPost = $param;
        $curl = curl_init();//初始化curl
        curl_setopt($curl, CURLOPT_URL,$postUrl);//抓取指定网页
        curl_setopt($curl, CURLOPT_HEADER, 0);//设置header
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);//要求结果为字符串且输出到屏幕上
        curl_setopt($curl, CURLOPT_POST, 1);//post提交方式
        curl_setopt($curl, CURLOPT_POSTFIELDS, $curlPost);
        $data = curl_exec($curl);//运行curl
        curl_close($curl);
        
        return $data;
    }

 
?>

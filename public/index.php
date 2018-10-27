<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
header('Access-Control-Allow-Headers: Authorization, Content-Type' );
header("access-control-allow-origin: *");
error_reporting(E_ALL);
ini_set('display_errors', 1);

require '../vendor/autoload.php';
require '../src/config/db.php';


$app = new \Slim\App();
//middleware
//middleware
$app->add(function (Request $request, Response $response,callable $next) {
		$header=$request->getHeader("authorization");
		if(count($header)>0)
		{
           $expl_left = explode(" ", $header[0]);
           if(count($expl_left)==2)
           {
              if(base64_decode($expl_left[1])=='esfera:vipan')
              { 
                 return $next($request, $response);
              }
              else
              {
                 $resarr =array(
                     'error'=>true,
                     'result'=>'',
                     'text'=>'Authentication failed***'
                  );
                  $newResponse = $response->withJson($resarr);
                  return $newResponse;
                
              }
           }
           else
           {
            $resarr =array(
                  'error'=>true,
                  'result'=>'',
                  'text'=>'Authentication failed**'
               );
             $newResponse = $response->withJson($resarr);
             return $newResponse;
           
           }
      }
      else
      {
         $resarr =array(
            'error'=>true,
            'result'=>'',
            'text'=>'Authentication failed*'
         );
        
        $newResponse = $response->withJson($resarr);
         return $newResponse;
      } 
});

/* Register Routes Path*/
require '../src/routes/customer.php';

$app->get('/', function (Request $request, Response $response, array $args) {
    $response->getBody()->write("Welcome to Home :)");
    return $response;
});



$app->post('/api/update_user_avatar',function(Request $request, Response $response,array $args){
	//$reqarr=$request->getParsedBody();
      $db = new db();
      $db=$db->connect();
           $files = $request->getUploadedFiles();

        if (empty($files['avatar'])) {
            throw new \RuntimeException('Expected a newfile');
        }

        $file = $files['avatar'];

        if ($file->getError() === UPLOAD_ERR_OK) {
            $fileName = $file->getClientFilename();
            //$x=$file->moveTo(__DIR__."/uploads/{$fileName}");
            $x=move_uploaded_file($_FILES['avatar']['tmp_name'],'../uploads/'.$fileName);

            return $response->withJson([
                'result' => [
                    'fileName' => $fileName,
                	'response'=>$x
                ],
            ])->withStatus(200);
        }

        return $response
            ->withJson([
                'error' => 'Nothing was uploaded'
            ])
            ->withStatus(415);
      	// $permitted=array('jpg','png','jpeg','gif');
      	// $filename=$_FILES['avatar']['name'];
      	// $file_size=$_FILES['avatar']['size'];
      	// $file_temp=$_FILES['avatar']['temp'];
      	// $div =explode('.', $filename);
      	// $file_ext=strtolower(end($div));
      	// $unique_image=substr(md5(time()),0,10).'.'.$file_ext;
      	
       // $folder= __DIR__.'/uploads/';
       // $dst=$folder.$filename;
      	// if(move_uploaded_file($file_temp,'./uploads/'))
      	// {
	      // 	$data=array(
	      // 		'avatar'=>$unique_image
	      // 	);
      	// 	 $db->where('id', $reqarr['user_id']);
       //         if ($db->update('Users', $data))
       //         {
       //            if($db->count==1)
       //            {
       //               $result=$db->count . ' record updated';
       //               $error=false;
       //               $text='Your Profile avatar successfully updated!!';
       //            }
       //            else
       //            {
       //               $error=true;
       //               $result='';
       //               $text='Update process has been denied';
       //            }
       //         }
       //         else
       //         {  
       //               $result='';
       //               $error=true;
       //               $text=$db->getLastError();
       //         }
       //  }
       //  else
       //  {
       //  		 	$result='';
       //               $error=true;
       //               $text='Unable to upload avatar';
       //  }
      	//  	 $resarr=array(
       //         'error'=>$error,
       //         'result'=>$dst,
       //         'text'=>$text
       //      );
         
       //     $newResponse = $response->withJson($resarr);
       //   return $newResponse;
      
});

$app->run();
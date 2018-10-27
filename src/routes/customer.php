<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use \Psr\Http\UploadedFile;
error_reporting(E_ALL);
ini_set('display_errors', 1);
header( 'Access-Control-Allow-Headers: Authorization, Content-Type' );
header("access-control-allow-origin: *");

$app = new \Slim\App;
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

//Routes 
$app->post('/api/registerUser',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();

      $db = new db();
     $db=$db->connect();
      if(count($reqarr)>0)
      {
         if(!array_key_exists('Firstname', $reqarr))
         {  
            
            $text="Please provide Firstname";
         }
         else if(!array_key_exists('Lastname', $reqarr))
         {
            
            $text="Please provide Lastname";
         }
         else if(!array_key_exists('Gender', $reqarr))
         {
            
            $text="Please provide Gender";
         }
         else if(!array_key_exists('email', $reqarr))
         {
            
            $text="Please provide email address";
         }
         else if(!array_key_exists('mobile_number', $reqarr))
         {
            
            $text="Please provide Mobile Number ex: countrycode+10digit number";
         }
         else if(!array_key_exists('password', $reqarr))
         {
            $text="Please provide Password value";
         }
         else 
         {
            
            $reqarr['confirmation_code']= mt_rand(1, 999999);
            $text='';
            //convert to MD5 Hash
            $usermobHash = md5($reqarr['mobile_number']);
            $reqarr['avatar']='http://www.gravatar.com/avatar/'.$usermobHash.'&d=mp';
            $reqarr['password']=md5($reqarr['password']);
         }
         if($text=='')
         {
            $db->where("mobile_number", $reqarr['mobile_number']);
            $user = $db->get("Users");
            if(count($user)>0)
            {
               $result='';
               $error=false;
               $text='User already exist with same mobile number!!';
            }
            else
            {
               $msg ='Please confirm your registeration after using confirmation code:'.$reqarr['confirmation_code'];
               $sms_response=send_sms($reqarr['mobile_number'],$msg);
               $id = $db->insert("Users", $reqarr);
               $result=$id;
               $error=false;
               $text='User successfully registered, please verify ';
            }
            $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
         }
         else 
         {
               $error=true;
               $resarr=array(
               'error'=>$error,
               'result'=>'',
               'text'=>$text
            );
         }
           $newResponse = $response->withJson($resarr);
         return $newResponse;
      }  
});


$app->post('/api/confirm_registration',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();
      $db = new db();
      $db=$db->connect();
      if(count($reqarr)>0)
      {
         if(!array_key_exists('user_id', $reqarr))
         {  
            
            $text="Please provide user_id";
         }
         else if(!array_key_exists('confirmation_code', $reqarr))
         {
            
            $text="Please provide confirmation code";
         }
         else 
         {
            $text='';
            
         }
         if($text=='')
         {
               $data=Array('verified'=>1);
               $db->where('id', $reqarr['user_id']);
               $db->where('confirmation_code', $reqarr['confirmation_code']);
               if ($db->update('Users', $data))
               {
                  if($db->count==1)
                  {
                     $result=$db->count . ' record updated';
                     $db->where('id', $reqarr['user_id']);
                     $db->update('Users', Array('confirmation_code'=>''));
                     $error=false;
                     $text='Your registration process has been successfully completed!!';
                  }
                  else
                  {
                     $error=true;
                     $result='';
                     $text='Update process has been denied';
                  }
               }
               else
               {  
                     $result='';
                     $error=true;
                     $text=$db->getLastError();
               }
             $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
         }
         else 
         {
               $error=true;
               $resarr=array(
               'error'=>$error,
               'result'=>'',
               'text'=>$text
            );
         }
           $newResponse = $response->withJson($resarr);
         return $newResponse;
      }  
});

$app->post('/api/change_password',function(Request $request, Response $response,array $args){
	  $reqarr=$request->getParsedBody();
      $db = new db();
      $db=$db->connect();
      if(count($reqarr)>0)
      {
         if(!array_key_exists('user_id', $reqarr))
         {  
            
            $text="Please provide user_id";
         }
         else if(!array_key_exists('currentpassword', $reqarr))
         {
            
            $text="Please provide currentpassword ";
         }
         else if(!array_key_exists('newpassword', $reqarr))
         {
            
            $text="Please provide newpassword";
         }
         else 
         {
            $text='';
            
         }
         if($text=='')
         {
              
                $db->where('id', $reqarr['user_id']);
                $db->where('password', md5($reqarr['currentpassword']));
               	$user=$db->get('Users');
               if (count($user)==1)
               {
               	 $data=array(
	                  'password'=>md5($reqarr['newpassword'])
	               );
	               $db->where('id', $reqarr['user_id']);
	               if ($db->update('Users', $data))
	               {
	                  if($db->count==1)
	                  {
	                     $result=$db->count . ' record updated';
	                     $error=false;
	                     $text='Your Password has been successfully changed!!';
	                  }
	                  else
	                  {
	                     $error=true;
	                     $result='';
	                     $text='Update process has been denied';
	                  }
	               }
	               else
	               {  
	                     $result=$status;
	                     $error=true;
	                     $text=$db->getLastError();
	                }
           		}
           		else
           		{
           				$result='';
	                     $error=true;
	                     $text='current password is not correct!!';
           		}
				$resarr=array(
				'error'=>$error,
				'result'=>$result,
				'text'=>$text
				);
         }
         else 
         {
               $error=true;
               $resarr=array(
               'error'=>$error,
               'result'=>'',
               'text'=>$text
            );
         }
           $newResponse = $response->withJson($resarr);
         return $newResponse;
      }
});

$app->post('/api/update_profile',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();
      $db = new db();
      $db=$db->connect();
      if(count($reqarr)>0)
      {
         if(!array_key_exists('user_id', $reqarr))
         {  
            
            $text="Please provide user_id";
         }
         else if(!array_key_exists('Firstname', $reqarr))
         {
            
            $text="Please provide Firstname ";
         }
         else if(!array_key_exists('Lastname', $reqarr))
         {
            
            $text="Please provide Lastname";
         }
         else if(!array_key_exists('email', $reqarr))
         {
            
            $text="Please provide email";
         }
          else if(!array_key_exists('Gender', $reqarr))
         {
            
            $text="Please provide Gender";
         }
         else 
         {
            $text='';
            
         }
         if($text=='')
         {
               $data=array(
                  'Firstname'=>$reqarr['Firstname'],
                  'Lastname'=>$reqarr['Lastname'],
                  'email'=>$reqarr['email'],
                  'Gender'=>$reqarr['Gender'],
               );
              
               $db->where('id', $reqarr['user_id']);
               if ($db->update('Users', $data))
               {
                  if($db->count==1)
                  {
                     $result=$db->count . ' record updated';
                     $error=false;
                     $text='Your Profile successfully updated!!';
                  }
                  else
                  {
                     $error=true;
                     $result='';
                     $text='Update process has been denied';
                  }
               }
               else
               {  
                     $result='';
                     $error=true;
                     $text=$db->getLastError();
               }
             $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
         }
         else 
         {
               $error=true;
               $resarr=array(
               'error'=>$error,
               'result'=>'',
               'text'=>$text
            );
         }
           $newResponse = $response->withJson($resarr);
         return $newResponse;
      }  
});

$app->post('/api/userlogin',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();

      $db = new db();
      $db=$db->connect();
      if(count($reqarr)>0)
      {
         if(!array_key_exists('mobile_number', $reqarr))
         {  
            $text="Please provide mobile_number";
         }
         else if(!array_key_exists('password', $reqarr))
         {
            
            $text="Please provide password";
         }
         else 
         {
            $text='';
            
         }
         if($text=='')
         {
              
               $db->where('mobile_number', $reqarr['mobile_number']);
               $db->where('password', md5($reqarr['password']));
               $user=$db->get('Users');
               if (count($user)==1)
               {
                  if($user[0]['verified']==1)
                  {
                     $error=false;
                     $result=array(
                        'user_id'=>$user[0]['id'],
                        'mobile_number'=>$user[0]['mobile_number'],
                        'Firstname'=>$user[0]['Firstname'],
                        'Lastname'=>$user[0]['Lastname'],
                        'avatar'=>$user[0]['avatar'],
                        'email'=>$user[0]['email'],
                        'Gender'=>$user[0]['Gender']
                     );
                      $text='User exist';
                  }
                  else
                  {
                     $error=true;
                     $result=array();
                     $text='Please confirm your account now';
                  }
               }
               else
               {  
                     $error=true;
                     $text='Invalid Mobile number and password!!';
                     $result=array();
               }
               $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
         }
         else 
         {
               $error=true;
              $resarr=array(
               'error'=>$error,
               'result'=>'',
               'text'=>$text
            );
         }
         $newResponse = $response->withJson($resarr);
         return $newResponse;
      }  
});

$app->get('/api/user_details/{user_id}',function(Request $request, Response $response,array $args){
             $db = new db();
               $db=$db->connect();
            $db->where('id', $args['user_id']);
               $user=$db->get('Users');
               if (count($user)==1)
               {
                 
                     $error=false;
                     $result=array(
                        'user_id'=>$user[0]['id'],
                        'mobile_number'=>$user[0]['mobile_number'],
                        'Firstname'=>$user[0]['Firstname'],
                        'Lastname'=>$user[0]['Lastname'],
                        'avatar'=>$user[0]['avatar'],
                        'email'=>$user[0]['email'],
                        'verified'=>$user[0]['verified'],
                         'Gender'=>$user[0]['Gender']
                     );
                      $text='User exist';
                 
               }
               else
               {  
                     $error=true;
                     $text='User does not exist!!';
                     $result=array();
               }
               $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
  $newResponse = $response->withJson($resarr);
         return $newResponse;
});
//create claim
$app->post('/api/createClaim',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();
      $db = new db();
     $db=$db->connect();
      if(count($reqarr)>0)
      {

            $text='';
      
         if($text=='')
         {
            
               $id = $db->insert("Claims", $reqarr);
               $result=$id;
               $error=false;
               $text='Claim successfully created!!';
            
               $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
         }
         else 
         {
               $error=true;
               $resarr=array(
               'error'=>$error,
               'result'=>'',
               'text'=>$text
            );
         }
           $newResponse = $response->withJson($resarr);
         return $newResponse;
      }  
});

$app->post('/api/add_thirdparty_toclaim',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();
      $db = new db();
     $db=$db->connect();
      if(count($reqarr)>0)
      {
			$ids = $db->insertMulti('Thirdparty_tbl', $reqarr);
			if(!$ids) {
				$error=true;
				$result='';
				$text='insert failed: ' . $db->getLastError();

			} else {

				$result=$ids;
				$error=false;
				$text='Thirparty list successfully added with following id\'s: ' . implode(', ', $ids);

			}
			$resarr=array(
			   'error'=>$error,
			   'result'=>$result,
			   'text'=>$text
			);
			$newResponse = $response->withJson($resarr);
			return $newResponse;
      }  
});


$app->post('/api/add_injured_toclaim',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();
      $db = new db();
      $db=$db->connect();
      if(count($reqarr)>0)
      {

			$ids = $db->insertMulti('Injured_person_tbl', $reqarr);
			if(!$ids) {
				$error=true;
				$result='';
				$text='insert failed: ' . $db->getLastError();

			} else {

				$result=$ids;
				$error=false;
				$text='Injured person list successfully added with following id\'s: ' . implode(', ', $ids);

			}
            $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
            );
            $newResponse = $response->withJson($resarr);
            return $newResponse;
      }  
});

$app->get('/api/get_claim_list/{user_id}',function(Request $request, Response $response,array $args){
      
              $db = new db();
               $db=$db->connect();
               $db->where('user_id', $args['user_id']);
               $claims=$db->get('Claims');
               if (count($claims)>=1)
               {
                  $mainarr=array();
                  for($i=0;$i<count($claims);$i++)
                  {
                        
                     $db->where('claim_id', $claims[$i]['id']);
                     $tp=$db->get('Thirdparty_tbl');
                     $claims[$i]['Thirparties']= $tp; 


                     $db->where('claim_id', $claims[$i]['id']);
                     $ip=$db->get('Injured_person_tbl');
                     $claims[$i]['Injured_persons']= $ip; 
                     array_push($mainarr, $claims[$i]);
                  }

                     $error=false;
                     $result=$mainarr;
                      $text='claim list available';
                 
               }
               else
               {  
                     $error=true;
                     $text='Claim list not found!!';
                     $result=array();
               }
               $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
  $newResponse = $response->withJson($resarr);
         return $newResponse;
        
});


$app->get('/api/get_claim_info/{claim_id}',function(Request $request, Response $response,array $args){
      
              $db = new db();
               $db=$db->connect();
               $db->where('id', $args['claim_id']);
               $claims=$db->get('Claims');
               if (count($claims)==1)
               {
                  $claim_id=$args['claim_id'];
                     $db->where('claim_id', $claims[0]['id']);
                     $tp=$db->get('Thirdparty_tbl');
                     $claims[0]['Thirparties']= $tp; 


                     $db->where('claim_id', $claims[0]['id']);
                     $ip=$db->get('Injured_person_tbl');
                     $claims[0]['Injured_persons']= $ip; 
                    
                  

                     $error=false;
                     $result=$claims[0];
                      $text="claim Info of [$claim_id] available";
                 
               }
               else
               {  
                     $error=true;
                     $text='Claim info not found!!';
                     $result=array();
               }
               $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
               );
 		 $newResponse = $response->withJson($resarr);
         return $newResponse;
        
});

$app->post('/api/delete_claim',function(Request $request, Response $response,array $args){
      		 $reqarr=$request->getParsedBody();
      		 $claim_id=$reqarr['claim_id'];
              $db = new db();
               $db=$db->connect();
               $db->where('id', $claim_id);
				if($db->delete('Claims')){
						
					$db->where('claim_id', $claim_id);
					if($db->delete('Thirdparty_tbl'))
					{
							$db->where('claim_id', $claim_id);
							$db->delete('Injured_person_tbl');
								 $error=false;
			                     $text='claim deleted successfully';
			               		 $result='';
					}
					else
					{
						 		$error=true;
			                     $text='Unable  to delete claim';
			               		 $result='';	
					}
             
		               $resarr=array(
		               'error'=>$error,
		               'result'=>$result,
		               'text'=>$text
		               );
				}
 		 $newResponse = $response->withJson($resarr);
         return $newResponse;
        
});

$app->post('/api/add_car_damage_part_toclaim',function(Request $request, Response $response,array $args){
      $reqarr=$request->getParsedBody();
      $db = new db();
     $db=$db->connect();
      if(count($reqarr)>0)
      {
      	$ids = $db->insertMulti('car_damage_part_tbl', $reqarr);
   		if(!$ids) {
   				$error=true;
   				$result='';
   				$text='insert failed: ' . $db->getLastError();
   		
   		} else {
   		   	   $result=$ids;
                  $error=false;
                  $text='Car damage parts successfully added following id\'s: ' . implode(', ', $ids);
   		}
           $resarr=array(
               'error'=>$error,
               'result'=>$result,
               'text'=>$text
            );
           $newResponse = $response->withJson($resarr);
         return $newResponse;
      }  
});

$app->post('/api/get_nearer_assessors',function(Request $request, Response $response,array $args){

      $reqarr=$request->getParsedBody();
      $db = new db();
      $db=$db->connect();
      if(count($reqarr)>0)
      {
         $d=array();
         $mainarr=array();
         $radius=$reqarr['radius']; //miles
         $currentlat=$reqarr['currentlat'];
         $currentlng=$reqarr['currentlng'];
         $cols = Array ("id", "Ins_coy_id", "Firstname","Lastname","email","License_number","address","city","state","zipcode","latitude","longitude","office_phone_number","cell_phone_number","Seniority","Business_id_number","image");
         $assessors=$db->get('assessors',null,$cols);
         if(count($assessors)>0)
         {
            for($i=0;$i<count($assessors);$i++)
            {

               $latitude=$assessors[$i]['latitude'];
               $longitude=$assessors[$i]['longitude'];
               $distance=distance($currentlat,$currentlng,$latitude,$longitude,'M');
               if($distance<=$radius)
               {
                  $db->where('id',$assessors[$i]['Ins_coy_id']);
                  $coyinfo=$db->get('Insurance_company');
                  $assessors[$i]['Insurance_company']=$coyinfo;
                  $assessors[$i]['distance_from_loc']=round($distance, 2);
                  $assessors[$i]['distance_in']='miles';
                   array_push($mainarr,$assessors[$i]);
               }
            }
         }
            if(count($mainarr)==0) {
               $error=true;
               $text='No available assessor near me';

            } else {
               $error=false;
               $text='list of assessors';
            }
           $resarr=array(
               'error'=>$error,
               'result'=>$mainarr,
               'text'=>$text
            );
         $newResponse = $response->withJson($resarr);
              return $newResponse;
      }
});


function distance($lat1, $lon1, $lat2, $lon2, $unit="M") {

  $theta = $lon1 - $lon2;
  $dist = sin(deg2rad($lat1)) * sin(deg2rad($lat2)) +  cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * cos(deg2rad($theta));
  $dist = acos($dist);
  $dist = rad2deg($dist);
  $miles = $dist * 60 * 1.1515;
  $unit = strtoupper($unit);
  if ($unit == "K") {
      return ($miles * 1.609344);
  } else if ($unit == "N") {
      return ($miles * 0.8684);
  } else {
      return $miles;
  }
}

function send_sms($dst,$text){
   $AUTH_ID = 'MANTZKNZK1ZMJKNGM4OG';
   $AUTH_TOKEN = 'ODQzN2QzYmY4YzIzMGY5NDY3YzY0ZTYyZDhiMjI1';
   $src = '919844108882';
   $url = 'https://api.plivo.com/v1/Account/'.$AUTH_ID.'/Message/';
   $data = array("src" => "$src", "dst" => "$dst", "text" => "$text");
   $data_string = json_encode($data);
   $ch=curl_init($url);
   curl_setopt($ch, CURLOPT_POST, true);
   curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
   curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
   curl_setopt($ch, CURLOPT_HEADER, false);
   curl_setopt($ch, CURLOPT_FRESH_CONNECT, true);
   curl_setopt($ch, CURLOPT_USERPWD, $AUTH_ID . ":" . $AUTH_TOKEN);
   curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
   $response = curl_exec( $ch );
   curl_close($ch);
   return $response;    
}
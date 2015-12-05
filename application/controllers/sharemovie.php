<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Sharemovie extends CI_Controller {

	public function __construct()
    {
      	parent::__construct();
        // Your own constructor code
        //$this->load->database('sharemovie');
        $this->movapikey='49b35ae23cb2dce9b78b40d209149e28';
        
    }

     private function getuserid($id)
	{
		
		$query = $this->db->query("select user_id from users where fb_id=".$this->db->escape($id));
		$result = $query->result();
		if($result)
		{
			foreach($result as $row)
			{
				$userid=$row->user_id;
			} 
			return $userid;
		}
		else
		{
			return false;
		}
		

	}


	public function login($accessToken)
	{

		$this->load->database('sharemovie');
		header('Content-type: application/json');


		// facebook url
		$service_url = 'https://graph.facebook.com/v2.4/me?access_token='.$accessToken.'&fields=id,name';

		//make the api call and store the response
		$curl = curl_init($service_url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		$curl_response = curl_exec($curl);
		curl_close($curl);

		//if the api call is failed
		if ($curl_response === false) {
		    //$info = curl_getinfo($curl);
		    //curl_close($curl);
		    //die('error occured during curl exec. Additioanl info: ' . var_export($info));
		    echo json_encode(array('error'=>'Unable to reach facebook servers'));
		    $this->db->close();
		    exit;

		}
		$decoded = json_decode($curl_response);
		
		//if the api call is success but error from facebook
		if (isset($decoded->error)) {
			//echo 'error';
		    //die('error occured: ' . $decoded->response->errormessage);
		    echo($curl_response);
		    $this->db->close();
		    exit;
		}

		$id = $decoded->id;
		$name = $decoded->name;
		//$pic = $decoded->picture->data->url;
		//echo $id;


		//insert a new record if it is a new user

		$query = $this->db->query("select * from users where fb_id=".$this->db->escape($id));

	    if($query -> num_rows() == 1)
	   	{
	     	$query = $this->db->query("update users set name=".$this->db->escape($name)." where fb_id=".$this->db->escape($id)); 

	     	//$userid = $this->getuserid($id);
		     
		      if($query)
		      {
		        echo json_encode(array('success'=>'User successfully logged in'));
		        $this->db->close();
		        exit;
		      }
		      else
		      {
		        echo json_encode(array('error'=>'Unable to execute query!'));
		        $this->db->close();
		        exit;
		      }
	   	}
	   	else
	   	{
		     $query = $this->db->query("insert into users(fb_id,name,crte_ts) 
		     values(".$this->db->escape($id).",".$this->db->escape($name).",CURRENT_TIMESTAMP)"); 
		     
		     //$userid = $this->getuserid($id);

		      if($query)
		      {
		        echo json_encode(array('success'=>'User successfully logged in'));		
		        $this->db->close();	        
		        exit;
		      }
		      else
		      {
		        echo json_encode(array('error'=>'Unable to execute query!'));
		        $this->db->close();
		        exit;
		      }
		 
	   	}

	/***************** END OF FUNCTION *****************/	
	}	

	public function getusergroups($id)
	{

		$this->load->database('sharemovie');
     	header('Content-type: application/json');

     	$id = $this->getuserid($id);

     	if($id==false)
     	{
     		echo json_encode(array('error'=>'Unable to authenticate!'));
		    $this->db->close();
		    exit;
     	}
     	
     	$query = $this->db->query("select a.group_id,a.name,b.cnt from 
		(select g.group_id,name from groups g join groupuser gu on g.group_id=gu.group_id 
		where gu.user_id=".$this->db->escape($id).")a
		join 
		(select g.group_id,count(*) cnt from groups g join groupuser gu on g.group_id=gu.group_id 
		group by g.group_id)b
		on a.group_id=b.group_id order by a.name");
	     	
     		
    	$result = $query->result();
    	$output = array();
				foreach($result as $row)
				{
					array_push($output,array('group_id'=>$row->group_id,
					'group_name'=>$row->name,
					'member_cnt'=>$row->cnt
					));
				} 
		echo json_encode(array('output'=>$output));
		$this->db->close();
		exit;
		
	/***************** END OF FUNCTION *****************/
	}



/***************** END OF CLASS *****************/
}



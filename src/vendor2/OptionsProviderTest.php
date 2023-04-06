<?php declare(strict_types=1);

require_once __DIR__.'/../classes/system.php';

use PHPUnit\Framework\TestCase;

final class SystemTest extends TestCase
{

    public function test_get_request_value(): void
    {
      $system = new SYSTEM();
	  $_GET['SCF']='SCF';
	  $_POST['ABC']='ABC';
      $this->assertSame($system->get_request_value($_GET['SCF']),'SCF');
	  $this->assertEmpty($system->get_request_value($_POST['ABC']));
	  $this->assertSame($system->get_request_value($_POST['ABC'],"","POST"),'ABC');

    }
	
	    
		public function test_request_get(): void
    {
      $system = new SYSTEM();
	  $_GET['SCF']='SCF';
	  $_POST['ABC']='ABC';
	  $default="default value";
      $this->assertSame($system->request_get($_GET['SCF']),'SCF');
	  $this->assertEmpty($system->request_get($_POST['ABC']));
	  $this->assertSame($system->request_get($_POST['ABC'],$default),$default);


    }
	    
		public function test_request_post(): void
    {
      $system = new SYSTEM();
	  $_GET['SCF']='SCF';
	  $_POST['ABC']='ABC';
	  $default="default value";
      $this->assertSame($system->request_post($_POST['ABC']),'ABC');
	  $this->assertEmpty($system->request_post($_GET['SCF']));
	  $this->assertSame($system->request_post($_GET['SCF'],$default),$default);


    }
		    
			public function test_request(): void
    {
      $system = new SYSTEM();
	  $_GET['SCF']='SCF';
	  $_POST['ABC']='ABC';
	  $_GET['NULL']=null;
	  $default="default value";
      $this->assertSame($system->request($_GET['SCF']),'SCF');
	  $this->assertSame($system->request($_POST['ABC']),'ABC');
	  $this->assertEmpty($system->request($_GET['NULL']));
	  $this->assertSame($system->request(@$_GET['UNDEFINED'],$default),$default);


    }
		    
			public function test_sanitize(): void
    {
      $system = new SYSTEM();
      $d1="$#@TEST---SRIng";
	  $d2="$#@1234#$---56--78";
      $this->assertSame($system->sanitize($d1),'test-sring');
	  $this->assertSame($system->sanitize($d2),'1234-56-78');


    }
			    
				public function test_get_array_key_value(): void
    {
      $system = new SYSTEM();
      $array['key']="value";
	  $array['test']="results";
      $this->assertSame($system->get_array_key_value($array,'key'),'value');
	  $this->assertSame($system->get_array_key_value($array,'no','default'),'default');


    }
				    
					public function test_in_multi_array_get_element_by_value(): void
    {
      $system = new SYSTEM();
      $array['key']['child']="value";
	  $array['test']['id']="results";
	  
      $this->assertFalse($system->in_multi_array_get_element_by_value('child',$array));
	  $this->assertSame($system->in_multi_array_get_element_by_value('results',$array),$array['test']);
	  $this->assertSame($system->in_multi_array_get_element_by_value('value', $array,'child'),$array['key']);
	  $this->assertFalse($system->in_multi_array_get_element_by_value('no', $array));


    }
	
	public function test_in_multi_array(): void
    {
      $system = new SYSTEM();
      $array['key']['child']="value";
	  $array['test']['id']="results";
	  
      $this->assertFalse($system->in_multi_array('child',$array));
	  $this->assertTrue($system->in_multi_array('results',$array));



    }
		public function test_copy_key_to_value(): void
    {
      $system = new SYSTEM();
      $array['key']['child']="value";
	    $array['test']['id']="results";

      $output=['key'=>'key','test'=>'test'];
      $output2=['test'=>'test','key'=>'key'];
	 
      $this->assertSame($system->copy_key_to_value($array),$output);
      $this->assertNotSame($system->copy_key_to_value($array),$output2);

    }

}




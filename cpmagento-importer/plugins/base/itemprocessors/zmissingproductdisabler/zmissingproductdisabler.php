<?php
require_once("../integration/inc/magmi_datapump.php");
class ZMissingProductDisabler extends Magmi_ItemProcessor
{
	/* START CONFIGURATION */
	protected $notficationToMailadres = "example@example.com";
	protected $notficationFromMailadres = "examplefrom@example.com";
	protected $notficationFromName = "Example Name";
	protected $setOutOfStock = true; // true if you want to set the product out of stock, enter false if you want to keep stock status intact
	/* END CONFIGURATION */
		
	protected $_toindex;
	protected $tns;
	protected $visinf;
	protected $catpaths;
	
	protected $sourceSkus;
	protected $sourceSkusObject;
	protected $i;
	protected $hoogstewaarde;
	protected $skuTypesList;
	
	public function getPluginInfo()
	{
		return array(
            "name" => "Missing-products disabler",
            "author" => "Marcel Vuijk",
            "version" => "0.0.4",
            "url"=>"http://www.emvee-solutions.com/blog/magmi-delete-disable-products-missing-csv-source/"
            );
	}

	public function getPluginParamNames()
	{
		return array();
	}

	static public function getCategory()
    {
        return "Input Data Preprocessing";
    }

    public function initialize($params)
    {
		$this->sourceSkus= array();
		$this->i = 0;
		$this->hoogstewaarde = 0;
    }
	
	public function beforeImport() {
		$this->log("Starting missing products disabler ","startup");	
	}
	
    public function processItemAfterId(&$item,$params = NULL)
    {   
		if($item['sku'])
			array_push($this->sourceSkus,$item['sku']);
	
		return true;
    }
	public function afterImport()
	{
		$profileName = 'Default'; // profile name as configured in Magmi which is used to import data
		$mode = 'update'; // import mode: update, create (is create en update), xcreate (only new creat)
		$type = 'simple'; // type product (simple, configurable, grouped, enz.)
	
		$dp=Magmi_DataPumpFactory::getDataPumpInstance("productimport");
		$dp->beginImportSession($profileName,$mode);
		
		//GET ALL ENABLED PRODUCTS
		$sql="SELECT sku FROM ".$this->tablename("catalog_product_entity")." p
			INNER JOIN ".$this->tablename("catalog_product_entity_int")." dt ON dt.entity_id = p.entity_id
			WHERE dt.attribute_id = (SELECT attribute_id FROM ".$this->tablename("eav_attribute")." WHERE attribute_code = 'status' LIMIT 1) AND dt.value = 1";
		
		$stmt=$this->select($sql);
		$count=0;
		$dbskus = array();
		while($result=$stmt->fetch())
		{
			//if more than one result, cannot match single sku
			array_push($dbskus,$result['sku']);
			$count++;
		}
		
		$diffs = array_diff($dbskus,$this->sourceSkus);
		
		$count = count($diffs);
		if($count > 0) {
			$ok = $this->send_email($this->notficationToMailadres,
					$this->notficationFromMailadres,
					$this->notficationFromName,
					'Products disabled on import',
					"Import: the following amount of products are diabled: ".$count);
			if(!$ok)
				$this->log("Cannot send email","error");
			else 
				$this->log("Mails sent ","startup");	
		}
			
		foreach($diffs as $diff) {
			$item['type'] = $type;
			$item['sku'] = $diff;
			//set qty and stock to zero
			if($this->setOutOfStock) {
				$item['qty'] = 0;
				$item['is_in_stock'] = 0;
			}
			//set status to disabled
			$item['status'] = 2;//2 means disabled, 1 is enabled, 0 = empty
			
			$dp->ingest($item);
			
			// end import session, will run post import plugins
			$this->log("<br/>Product $diff not found in data source and disabled in Magento","startup");				
		}//end foreach
		$dp->endImportSession();
			
	}
	public function send_email($to, $from, $from_name, $subject, $message, $attachments=false)
	{
		$headers = "From: ".$from_name."<".$from.">\n";
		$headers .= "Reply-To: ".$from_name."<".$from.">\n";
		$headers .= "Return-Path: ".$from_name."<".$from.">\n";
		$headers .= "Message-ID: <".time()."-".$from.">\n";
		$headers .= "X-Mailer: PHP v".phpversion();

		$msg_txt="";
		$email_txt = $message;

		$semi_rand = md5(time());
		$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";

		$headers .= "\nMIME-Version: 1.0\n" .
"Content-Type: multipart/mixed;\n" .
" boundary=\"{$mime_boundary}\"";

		$email_txt .= $msg_txt;
		$email_message = $email_txt;
		$email_message .= "This is a multi-part message in MIME format.\n\n" .
"--{$mime_boundary}\n" .
"Content-Type:text/html; charset=\"iso-8859-1\"\n" .
"Content-Transfer-Encoding: 7bit\n\n" .
		$email_txt . "\n\n";

		$email_message .= "--{$mime_boundary}--\n";
		$this->log("Sending mail notification to : $to","info");
		$ok= mail($to, $subject, $email_message, $headers);
		return $ok;
	}
	
}



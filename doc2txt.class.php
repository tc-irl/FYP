<!--

This class was submitted by Tony Cullen as part of the Acronym Identification System. 
This class 

This class is downloaded from: http://www.phpclasses.org/package/7934-PHP-Convert-MS-Word-Docx-files-to-text.html

This class is used to convert any doc,docx file to simple text format.

author: Gourav Mehta
author's email: gouravmehta@gmail.com
author's phone: +91-9888316141 
-->

<?php

class Doc2Txt 
{
	private $filename;
	
	public function __construct($filePath) {
		$this->filename = $filePath;
	}
	
	// Function for reading documents of .doc format
	private function read_doc()	{
		$fileHandle = fopen($this->filename, "r"); // open the file
		$line = @fread($fileHandle, filesize($this->filename));  //read the file
		$lines = explode(chr(0x0D),$line); // explode the file into an array
		$outtext = ""; 

		// loop through the array to handle the file, deleting empty lines and irrelevant data
		foreach($lines as $thisline)
		  {
			$pos = strpos($thisline, chr(0x00));
			if (($pos !== FALSE)||(strlen($thisline)==0))
			  {
			  } else {
				$outtext .= $thisline." ";
			  }
		  }
		 $outtext = preg_replace("/[^a-zA-Z0-9\s\,\.\-\n\r\t@\/\_\(\)]/","",$outtext); // handling the text, removing the relevant characters
		return $outtext;
	}

	// Function for reading documents of .doc format
	private function read_docx(){

		$striped_content = '';
		$content = '';

		$zip = zip_open($this->filename); // open the file

		if (!$zip || is_numeric($zip)) return false;

		// loop to read the file
		while ($zip_entry = zip_read($zip)) {

			if (zip_entry_open($zip, $zip_entry) == FALSE) continue;

			if (zip_entry_name($zip_entry) != "word/document.xml") continue;

			$content .= zip_entry_read($zip_entry, zip_entry_filesize($zip_entry)); // append the content to the $content variable

			zip_entry_close($zip_entry);
		} // end while

		zip_close($zip); // close the file

		// handling the text, replacing the relevant characters with a space or a new line

		$content = str_replace('</w:r></w:p></w:tc><w:tc>', " ", $content);
		$content = str_replace('</w:r></w:p>', "\r\n", $content); 
		$striped_content = strip_tags($content);

		return $striped_content;
	}
	
	// Function for reading documents of .doc format
	public function convertToText() {
	
		if(isset($this->filename) && !file_exists($this->filename)) {
			return "File Not exists";
		}
		
		$fileArray = pathinfo($this->filename); // gets file information
		$file_ext  = $fileArray['extension']; // gets the file extension based on the file information

		// checking if the file extension is doc or docx and then calls either read_doc() or read_docx() function, in turn returning the handled text. 
		
		if($file_ext == "doc" || $file_ext == "docx")
		{
			if($file_ext == "doc") {
				return $this->read_doc();
			} else {
				return $this->read_docx();
			}
		} else {
			return "Invalid File Type";
		}
	}
}
?>
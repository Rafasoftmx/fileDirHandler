<?php

/*
   __ _ _      _____  _      _    _                 _ _           
  / _(_) |    |  __ \(_)    | |  | |               | | |          
 | |_ _| | ___| |  | |_ _ __| |__| | __ _ _ __   __| | | ___ _ __ 
 |  _| | |/ _ \ |  | | | '__|  __  |/ _` | '_ \ / _` | |/ _ \ '__|
 | | | | |  __/ |__| | | |  | |  | | (_| | | | | (_| | |  __/ |   
 |_| |_|_|\___|_____/|_|_|  |_|  |_|\__,_|_| |_|\__,_|_|\___|_|   
  _____        __                 __ _     ___   ___  __  ___     
 |  __ \      / _|               / _| |   |__ \ / _ \/_ |/ _ \    
 | |__) |__ _| |_ __ _ ___  ___ | |_| |_     ) | | | || | (_) |   
 |  _  // _` |  _/ _` / __|/ _ \|  _| __|   / /| | | || |\__, |   
 | | \ \ (_| | || (_| \__ \ (_) | | | |_   / /_| |_| || |  / /    
 |_|  \_\__,_|_| \__,_|___/\___/|_|  \__| |____|\___/ |_| /_/     
                                                                  
                                                             
                                                               
                                                                            

* Simple class to handle some useful directory and file functions and zip compression


*/

class fileDirHandler{	

	
	/**
	 * The route in the file system
	 * @var string
	 */
	
	public  $permissions = 0755; // dir or file access permissions used when create
	//0600 -Read and write for owner, nothing for everybody else
	//0644 -Read and write for owner, read for everybody else
	//0755 -Everything for owner, read and execute for others
	//0750 -Everything for owner, read and execute for owner's group
	
	
	private $path = "";	//The set route in the file system
	public $parentDir = ""; //Parent directory's path
	public $name = ""; //name of the file or directory without extension
	public $fileName = ""; //name of the file or directory, if is file contain extension
	public $extension = ""; //file extension if has	
	public $mimeType = ""; // Multipurpose Internet Mail Extensions,containing a type and a subtype in a string e.g. .jpg image is "image/jpeg"
	public $type = ""; //type of path directory or file (dir/file)
	public $exist = false; // if the file or directory exist 
	

	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 */
	public function __construct($path = ""){
		$this->SetPath($path);		
	}	
	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 */
	private function is_arrayListFiles($arr){
		if(is_array($arr))
		{
			if(count($arr)>0)
			{
				if(is_array($arr[0]))
				{
					// if is associative array from this class
					if(array_diff_key($arr[0],array_keys(array_keys($arr[0]))))
					{
						if(array_key_exists("fullpath", $arr[0]))// it means is an array from this class
						{
							return true;
						}
					}					
				}

			}
		}
		return false;
	}	
	

	
	/**
	 * function for Sort an arry based on the strings length, using "uasort" in function Copy() of the class
	 *
	 * @access private
	 * @param string $val_1
	 * @param string $val_2
	 * @return int
	 */
	private function lengthSort($val_1, $val_2){

		// initialize the return value to zero 
		$retVal = 0;
		
		// compare lengths 
		$firstVal = strlen($val_1); 
		$secondVal = strlen($val_2);
		
		if($firstVal > $secondVal) 
		{ 
			$retVal = 1; 
		} 
		else if($firstVal < $secondVal) 
		{ 
			$retVal = -1; 
		} 
		return $retVal; 

	}
	
	/**
	 * Set the explorer path
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	private function fixPath($path="")
	{
		$path = str_replace("\\", "/", $path);
		$path = (substr($path, -1) == "/") ? substr($path, 0, -1) : $path;

		return $path;
	}
	
	/**
	 * Get MIME Type of a file
	 *
	 * @access public
	 * @param string $file
	 * @return string
	 */
	public static function getMimeType($file = "")
	{
		
		if($file == "")
		{
			return;
		}
		
		$ruteParts = pathinfo(strtolower($file));
		
		
		
		global $fileDirHandler_mimeT;
		
		if (array_key_exists("extension",$ruteParts))
		{
			if (array_key_exists($ruteParts["extension"],$fileDirHandler_mimeT))
			{			
				return $fileDirHandler_mimeT[$ruteParts["extension"]];
			}
		}
		

		if(is_file( $file ) )
		{
			return mime_content_type($file);
		}
		
		return "";	
	}
	
	
	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	private function is_dirEmpty($dir)
	{		
		$handle = opendir($dir);
		  while (false !== ($entry = readdir($handle))) {
			if ($entry != "." && $entry != "..") {
			  closedir($handle);
			  return FALSE;
			}
		  }
		  closedir($handle);
		  return TRUE;
	}
	
	/**
	 * get the current path
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function getPath(){
		return $this->path;
	}


	/**
	 * Set the explorer path
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function SetPath($path="")
	{		
		if($path != ""){ 			
			$this->path = $this->fixPath($path);
		}
		else{
			return;
		}
		
		$ruteParts = pathinfo($this->path);


		
		$this->parentDir  = $ruteParts['dirname']; //Parent directory's path
		$this->fileName = $ruteParts['basename']; //with extension
		$this->name = $ruteParts['filename']; //without extension
		if (array_key_exists("extension",$ruteParts))
		{
			$this->extension = strtolower($ruteParts['extension']);
			$this->mimeType = $this->getMimeType($this->path);
			$this->type ="file"; // define earlier just in case file no exist, it's supposition
		}
		else
		{
			$this->type ="dir"; // define earlier just in case file no exist, it's supposition
			
		}	
		
		
		
		if(is_file ($this->path))// if file exist
		{
			$this->type ="file";
			$exist = true;
		}
		else if(is_dir($this->path))// if file exist
		{
			$this->type ="dir";
			$exist = true;
		}
		
		
		
	}

	/**
	 * Read file content using fopen and fread, return all file contents
	 * 
	 * @access public
	 * @param string $filename
	 * @return string
	 */
	public function Read(){
		$handle = fopen($this->path, "r");
		$contents = fread($handle, filesize($this->path));
		fclose($handle);
		return $contents;
	}
	
	/**
	 * Write string content to file using fopen and fwrite
	 * 
	 * @access public
	 * @param string $filename
	 * @param string $contents
	 * @return bool
	 */
	public function Write($contents){
		if($handle = fopen($this->path,"w")){
			fwrite($handle, $contents); 
			fclose($handle); 
			return true;
		}
		return false;
	}
	
	/**
	 * create directories and files, creates the parent directories if they doesn't exist
	 * 
	 * @access public
	 * @param bool $is_dir 
	 * @param bool $overwrite If true exist file is overwrited
	 * @return bool
	 */
	public function Create($is_dir=false, $overwrite =false){
		if(file_exists($this->path) && !$overwrite ) return false;
		if(!$is_dir){
			$parts = explode("/", $this->path);
			$path = "";
			foreach ($parts as $part){
				if($part == end($parts)) break;
				$path .= $part . "/";
				@mkdir($path, $this->permissions);
			}
			if($handle = fopen($this->path, 'w')){
				fclose($handle);
			}
		}else{
			$parts = explode("/", $this->path);
			$path = "";
			foreach ($parts as $part){
				$path .= $part . "/";
				@mkdir($path, $this->permissions);
			}
		}
		return file_exists($this->path);
	}
	
	/**
	 * Delete a file or directory
	 * If it's a directory all the content will also be deleted.
	 * 
	 * @access public
	 * @return bool
	 */
	public function Delete(){
		if(is_dir($this->path) && $this->path != ""){
			$result = $this->Listing();
			
			// makes a map and sort them to progressive deletion, from files to directories
			$sort_result = array();
			foreach($result as $item){
				if($item['type'] == "file"){
					array_unshift($sort_result, $item);
				}else{
					$sort_result[] = $item;
				}
			}
			
			$trys =0;
			// Start deleting
			while(file_exists($this->path)){
				if(is_array($sort_result)){
					foreach($sort_result as $item){
						if($item['type'] == "file"){
							@unlink($item['fullpath']);
						}else{
							@rmdir($item['fullpath']);
						}
					}
				}
				@rmdir($this->path);
				
				$trys++;
				if ($trys >= 2){break;}
				
			}
			return !file_exists($this->path);
		}else{
			@unlink($this->path);
			return !file_exists($this->path);
		}
	}
	
	/**
	 * Copy directory's or files
	 *
	 * @access public
	 * @param string $destination
	 * @param bool $create
	 * @return bool
	 */
	public function Copy($destination){
		if($destination == "") throw new Exception("Destination is not specified.");
			
		$destination = $this->fixPath($destination);		
	
		if(is_dir($this->path)){
			
			// Create paths recursively
			$result = $this->Listing();
			$paths = array();
			$files = array();
			foreach ($result as $item){
				if($item["type"] == "dir"){
					$paths[] = str_replace($this->path, "", $item['fullpath']);
				}else{
					$file = str_replace($this->path, "", $item['fullpath']);
					$files[] = (substr($file, 0, 1) == "/") ? $file : "/" . $file;
				}
			}
			uasort($paths, array($this, "lengthSort"));
			
			// Create directory structure
			foreach ($paths as $path){
				$path = (substr($path, 0, 1) == "/") ? $path : "/" . $path;
				$new_directory = $destination . $path;
				@mkdir($destination, $this->permissions);
				if(!file_exists($new_directory)){
					@mkdir($new_directory, $this->permissions);
				}
			}
			
			// Copy files
			foreach ($files as $file){
				@copy($this->path . $file, $destination . $file);
			}
			return file_exists($destination);
		}else{
			@copy($this->path, $destination);
			return file_exists($destination);
		}

	}
	
	/**
	 * Move directory or file
	 * 
	 * @access public
	 * @param string $destination
	 * @access void
	 */
	public function Move($destination){
		$this->Copy($destination);
		$this->Delete();
		return (file_exists($destination) && !file_exists($this->path));
	}
	
	/**
	 * List directory content
	 * 
	 * @access public
	 * @param array $exclude
	 * @param bool $recursive
	 * @return array
	 */
	public function Listing($recursive=true, $exclude_extension=array(), $exclude_file=array(), $exclude_dir=array(), &$list=array(), $dir=""){

		// Lowercase excluded arrays
		$exclude_extension = array_map("strtolower", $exclude_extension);
		$exclude_file = array_map("strtolower", $exclude_file);
		$exclude_dir = array_map("strtolower", $exclude_dir);
		
		$dir = ($dir == "") ? $this->path : $dir;
		if(substr($dir, -1) != "/") $dir .= "/";

		// Open folder 
		$dir_handle = @opendir($dir) or die("Unable to open $dir"); 

		// Loop files 
		while ($file = readdir($dir_handle)) { 
			
		$extension="";	
		$ruteParts = pathinfo($dir . $file);

		if (array_key_exists("extension",$ruteParts))
		{
			$extension = $ruteParts['extension'];
		}	
			
			
			// omit dots and extension excluded
			if($file == "." || $file == ".." || in_array($extension, $exclude_extension)) continue; 
			
			if(is_dir($dir . $file)){
				if(!in_array(strtolower($file), $exclude_dir)){
					$info				= array();
					$info["type"]		= "dir";
					$info["parentDir"]	= $dir;
					$info["name"]		= $file; 
					$info["fullpath"]	= $dir . $file;
					$list[] = $info;
				}
			}else{
				if(!in_array(strtolower($file), $exclude_file)){
					$info				= array();					
					$info["type"]		= "file";
					$info["parentDir"]	= $dir;
					$info["fileName"]	= $file; //with extension
					$info["name"]		= $ruteParts['filename']; //without extension
					$info["extension"]  = $extension;
					$info["mimeType"]   = $this->getMimeType($dir . $file);
					$info["fullpath"]	= $dir . $file;
					$list[] = $info;
				}
			}
			
			if($recursive && is_dir($dir . $file) && !in_array(strtolower($file), $exclude_dir)){
				$this->Listing($recursive, $exclude_extension, $exclude_file, $exclude_dir, $list, $dir . $file);
			}
			
		} 
		
		// Close 
		closedir($dir_handle); 
		
		return $list;
		
	}
	

	/**
	 * Move directory or file
	 * 
	 * @access public
	 * @param string $destination
	 * @access void
	 */
	private function getZipFilesPath($file=null,$fullpath="",$type="",$rootPath="",$includeSelfFolder = false,$createOnPath = "")
	{
				
		if($file == null)
		{
			$file= array();
		}
		
		if (!array_key_exists("fullpath",$file))
		{
			$file["fullpath"] =$fullpath;
		}
		if (!array_key_exists("type",$file))
		{
			$file["type"] =$type;
		}
		
		
		if($rootPath == "")
		{			
			$ruteParts = pathinfo($file["fullpath"]);
			
			if($createOnPath != "")
			{
				$file["ZipFilePath"] = $this->fixPath($createOnPath)."/".$ruteParts["basename"];
			}
			else
			{
				$file["ZipFilePath"] = $ruteParts["basename"];
			}			
		}
		else
		{	
			$pathToReplace = '/'.preg_quote($rootPath, '/').'/';// erase "rootPath" from "file Path"
			$newFilePath = preg_replace($pathToReplace,"",$file["fullpath"],1);
			
			$newFilePath = (substr($newFilePath, 0,1) == "/") ? substr($newFilePath, 1,strlen($newFilePath)) : $newFilePath;	
			
			
			
			if($includeSelfFolder)
			{
				$ruteParts = pathinfo($rootPath);
				$SelfFolder = $ruteParts["basename"];
				
				
				if($createOnPath!= "" && $SelfFolder !="" )
				{
					$file["ZipFilePath"] = $this->fixPath($createOnPath)."/".$SelfFolder."/".$newFilePath;
				}
				elseif($SelfFolder != "")
				{
					$file["ZipFilePath"] = $SelfFolder."/".$newFilePath;
				}
				else
				{
					$file["ZipFilePath"] = $newFilePath;
				}
				
			}
			else
			{
				if($createOnPath != "")
				{
					$file["ZipFilePath"] = $this->fixPath($createOnPath)."/".$newFilePath;
				}
				else
				{
					$file["ZipFilePath"] = $newFilePath;
				}
				
			}

			
		}
		
		return $file;
	}
	

	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	private function makeFileList($files = null,$recursive = true,$includeSelfFolder = false,$createOnPath = "",$rootPath="",$exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{	
		$listFiles =  array();
		
		if($files != null)
		{
			if(is_array($files))
			{
				if($this->is_arrayListFiles($files))// if is associative array from this class
				{
					foreach($files as $item)
					{	
						if($item["type"] == "file")// is file
						{
							$listFiles[] = $this->getZipFilesPath($item,"","",$rootPath,$includeSelfFolder,$createOnPath);
						}
						else// is dir
						{				
							$rootPathDir =$this->getZipFilesPath($item,"","",$rootPath,$includeSelfFolder,$createOnPath);
							if($rootPathDir["fullpath"] != "")
							{
								$listFiles[] = $rootPathDir;
							}
							
							if($recursive)// if is recursive
							{
								$fdh = new fileDirHandler($item["fullpath"]);
								$dirFiles = $fdh->Listing($recursive, $exclude_extension, $exclude_file, $exclude_dir);

								foreach($dirFiles as $dirFile)
								{
									$listFiles[] = $this->getZipFilesPath($dirFile,"","",$rootPath,$includeSelfFolder,$createOnPath);
								}
							}
							
						}

					}
				}
				else// normal array
				{
					foreach($files as $item)
					{
						$item = $this->fixPath($item);
						
						if(is_file ($item))// is file
						{
							$listFiles[] = $this->getZipFilesPath(null,$item,"file",$rootPath,$includeSelfFolder,$createOnPath);
						}
						elseif(is_dir($item))// is dir
						{
							$rootPathDir = $this->getZipFilesPath(null,$item,"dir",$rootPath,$includeSelfFolder,$createOnPath);
							if($rootPathDir["fullpath"] != "")
							{
								$listFiles[] = $rootPathDir;
							}
							
							if($recursive) //if is recursive
							{
								$fdh = new fileDirHandler($item);
								$dirFiles = $fdh->Listing($recursive, $exclude_extension, $exclude_file, $exclude_dir);

								foreach($dirFiles as $dirFile)
								{
									$listFiles[] = $this->getZipFilesPath($dirFile,"","",$rootPath,$includeSelfFolder,$createOnPath);
								}
							}
						}
						else
						{
							$ruteParts = pathinfo($item);

							if (array_key_exists("extension",$ruteParts))
							{
								$listFiles[] = $this->getZipFilesPath(null,$item,"file",$rootPath,$includeSelfFolder,$createOnPath);
							}
							else
							{
								$listFiles[] = $this->getZipFilesPath(null,$item,"dir",$rootPath,$includeSelfFolder,$createOnPath);
							}
							
						}
					}
				}
			}
			elseif(is_string ($files))
			{
				$stringFiles = explode(",", $files);
				
				foreach($stringFiles as $item)
				{
					$item = $this->fixPath($item);
					
					if(is_file ($item))// is file
					{
						$listFiles[] = $this->getZipFilesPath(null,$item,"file",$rootPath,$includeSelfFolder,$createOnPath);
					}
					elseif(is_dir($item))// is dir
					{
						
						$rootPathDir = $this->getZipFilesPath(null,$item,"dir",$rootPath,$includeSelfFolder,$createOnPath);
						if($rootPathDir["fullpath"] != "")
						{
							$listFiles[] = $rootPathDir;
						}						
						
						if($recursive)//if is recursive
						{
							$fdh = new fileDirHandler($item);
							$dirFiles = $fdh->Listing($recursive, $exclude_extension, $exclude_file, $exclude_dir);
							foreach($dirFiles as $dirFile)
							{
								$listFiles[] = $this->getZipFilesPath($dirFile,"","",$rootPath,$includeSelfFolder,$createOnPath);
							}
						}
					}
					else
					{
						$ruteParts = pathinfo($item);

						if (array_key_exists("extension",$ruteParts))
						{
							$listFiles[] = $this->getZipFilesPath(null,$item,"file",$rootPath,$includeSelfFolder,$createOnPath);
						}
						else
						{
							$listFiles[] = $this->getZipFilesPath(null,$item,"dir",$rootPath,$includeSelfFolder,$createOnPath);
						}	
					}
				}
			}

		}

		
		return $listFiles;
		
	}

	

	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	public function zipCreate($zipName = "",$overwrite = false,$recursive = true,$includeSelfFolder = false,$includeEmptyFolders= true, $createOnPath = "",$fromFiles= null ,$exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{
		$flags = ($overwrite ? ZIPARCHIVE::CREATE | ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE);
		$files= array();
		$dirs= array();
		
		if($fromFiles!== null)
		{
			if($recursive)
			{
				$listFiles = $this->makeFileList($fromFiles,false,$includeSelfFolder,$createOnPath,"",$exclude_extension,$exclude_file,$exclude_dir);
				
				foreach($listFiles as  $item)
				{
					if($item["type"] == "file")
					{
						$files[] = $item;
					}
					else
					{						
						$files = array_merge(
							$files,
							$this->makeFileList($item["fullpath"],$recursive,$includeSelfFolder,$createOnPath,$item["fullpath"],$exclude_extension,$exclude_file,$exclude_dir));
					}
				}
				
				
			}
			else
			{
				$files = $this->makeFileList($fromFiles,$recursive,$includeSelfFolder,$createOnPath,"",$exclude_extension,$exclude_file,$exclude_dir);
			}
			
			
			
		}		
		elseif(is_dir($this->path))
		{			
			$files = $this->makeFileList($this->path,$recursive,$includeSelfFolder,$createOnPath,$this->path,$exclude_extension,$exclude_file,$exclude_dir);
		}
		elseif(is_file ($this->path))
		{
			$files = $this->makeFileList($this->path,false,false,$createOnPath,"",$exclude_extension,$exclude_file,$exclude_dir);
		}		
	

		
		
		if(count($files)>0)
		{
			$zip = new ZipArchive;
			if ($zip->open($zipName, $flags) === true)
			{
				foreach($files as $file)
				{
					
					if($file["type"] == "file")
					{
						$zip->addFile($file["fullpath"],$file["ZipFilePath"]);
					}
					else
					{
						
						if($includeEmptyFolders)
						{							
							$zip->addEmptyDir ($file["ZipFilePath"]);	
						}						
					}
				}
			}

			$zip->close();
		}
		

		
		
	}
	
	
	
	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	public function zipListing($recursive=true, $exclude_extension=array(), $exclude_file=array(), $exclude_dir=array(), &$list=array(), $dir="")
	{	
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) {
			for( $i = 0; $i < $zip->numFiles; $i++ ){ 
				$stat = $zip->statIndex( $i ); 
				echo   $stat['name'] . "<br>"; 
			}
		} 
		
	}
	
	
	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	public function zipDelete($files = null)
	{

		
	}
	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	public function zipCopy($files = null,$zipFile = "",$newPath = "")
	{		
		
	}
	
	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	public function zipMove($files = null,$zipFile = "",$newPath = "")
	{		
		
	}

	/**
	 * Zip file or directory
	 *
	 * @access public
	 * @return bool
	 */
	public function zipExtract($files = null,$extractionPath = "")
	{		
		
	}
	
}



// extra:

//find file
// rename
 //zip coments




$fileDirHandler_mimeT = array();

$fileDirHandler_mimeT['123'] = 'application/vnd.lotus-1-2-3';
$fileDirHandler_mimeT['3dml'] = 'text/vnd.in3d.3dml';
$fileDirHandler_mimeT['3g2'] = 'video/3gpp2';
$fileDirHandler_mimeT['3gp'] = 'video/3gpp';
$fileDirHandler_mimeT['7z'] = 'application/x-7z-compressed';
$fileDirHandler_mimeT['aab'] = 'application/x-authorware-bin';
$fileDirHandler_mimeT['aac'] = 'audio/x-aac';
$fileDirHandler_mimeT['aam'] = 'application/x-authorware-map';
$fileDirHandler_mimeT['aas'] = 'application/x-authorware-seg';
$fileDirHandler_mimeT['abw'] = 'application/x-abiword';
$fileDirHandler_mimeT['ac'] = 'application/pkix-attr-cert';
$fileDirHandler_mimeT['acc'] = 'application/vnd.americandynamics.acc';
$fileDirHandler_mimeT['ace'] = 'application/x-ace-compressed';
$fileDirHandler_mimeT['acu'] = 'application/vnd.acucobol';
$fileDirHandler_mimeT['adp'] = 'audio/adpcm';
$fileDirHandler_mimeT['aep'] = 'application/vnd.audiograph';
$fileDirHandler_mimeT['afp'] = 'application/vnd.ibm.modcap';
$fileDirHandler_mimeT['ahead'] = 'application/vnd.ahead.space';
$fileDirHandler_mimeT['ai'] = 'application/postscript';
$fileDirHandler_mimeT['aif'] = 'audio/x-aiff';
$fileDirHandler_mimeT['air'] = 'application/vnd.adobe.air-application-installer-package+zip';
$fileDirHandler_mimeT['ait'] = 'application/vnd.dvb.ait';
$fileDirHandler_mimeT['ami'] = 'application/vnd.amiga.ami';
$fileDirHandler_mimeT['apk'] = 'application/vnd.android.package-archive';
$fileDirHandler_mimeT['application'] = 'application/x-ms-application';
$fileDirHandler_mimeT['apr'] = 'application/vnd.lotus-approach';
$fileDirHandler_mimeT['asf'] = 'video/x-ms-asf';
$fileDirHandler_mimeT['aso'] = 'application/vnd.accpac.simply.aso';
$fileDirHandler_mimeT['atc'] = 'application/vnd.acucorp';
$fileDirHandler_mimeT['atom'] = 'application/atom+xml';
$fileDirHandler_mimeT['atomcat'] = 'application/atomcat+xml';
$fileDirHandler_mimeT['atomsvc'] = 'application/atomsvc+xml';
$fileDirHandler_mimeT['atx'] = 'application/vnd.antix.game-component';
$fileDirHandler_mimeT['au'] = 'audio/basic';
$fileDirHandler_mimeT['avi'] = 'video/x-msvideo';
$fileDirHandler_mimeT['aw'] = 'application/applixware';
$fileDirHandler_mimeT['azf'] = 'application/vnd.airzip.filesecure.azf';
$fileDirHandler_mimeT['azs'] = 'application/vnd.airzip.filesecure.azs';
$fileDirHandler_mimeT['azw'] = 'application/vnd.amazon.ebook';
$fileDirHandler_mimeT['bcpio'] = 'application/x-bcpio';
$fileDirHandler_mimeT['bdf'] = 'application/x-font-bdf';
$fileDirHandler_mimeT['bdm'] = 'application/vnd.syncml.dm+wbxml';
$fileDirHandler_mimeT['bed'] = 'application/vnd.realvnc.bed';
$fileDirHandler_mimeT['bh2'] = 'application/vnd.fujitsu.oasysprs';
$fileDirHandler_mimeT['bin'] = 'application/octet-stream';
$fileDirHandler_mimeT['bmi'] = 'application/vnd.bmi';
$fileDirHandler_mimeT['bmp'] = 'image/bmp';
$fileDirHandler_mimeT['box'] = 'application/vnd.previewsystems.box';
$fileDirHandler_mimeT['btif'] = 'image/prs.btif';
$fileDirHandler_mimeT['bz'] = 'application/x-bzip';
$fileDirHandler_mimeT['bz2'] = 'application/x-bzip2';
$fileDirHandler_mimeT['c'] = 'text/x-c';
$fileDirHandler_mimeT['c11amc'] = 'application/vnd.cluetrust.cartomobile-config';
$fileDirHandler_mimeT['c11amz'] = 'application/vnd.cluetrust.cartomobile-config-pkg';
$fileDirHandler_mimeT['c4g'] = 'application/vnd.clonk.c4group';
$fileDirHandler_mimeT['cab'] = 'application/vnd.ms-cab-compressed';
$fileDirHandler_mimeT['car'] = 'application/vnd.curl.car';
$fileDirHandler_mimeT['cat'] = 'application/vnd.ms-pki.seccat';
$fileDirHandler_mimeT['ccxml'] = 'application/ccxml+xml,';
$fileDirHandler_mimeT['cdbcmsg'] = 'application/vnd.contact.cmsg';
$fileDirHandler_mimeT['cdkey'] = 'application/vnd.mediastation.cdkey';
$fileDirHandler_mimeT['cdmia'] = 'application/cdmi-capability';
$fileDirHandler_mimeT['cdmic'] = 'application/cdmi-container';
$fileDirHandler_mimeT['cdmid'] = 'application/cdmi-domain';
$fileDirHandler_mimeT['cdmio'] = 'application/cdmi-object';
$fileDirHandler_mimeT['cdmiq'] = 'application/cdmi-queue';
$fileDirHandler_mimeT['cdx'] = 'chemical/x-cdx';
$fileDirHandler_mimeT['cdxml'] = 'application/vnd.chemdraw+xml';
$fileDirHandler_mimeT['cdy'] = 'application/vnd.cinderella';
$fileDirHandler_mimeT['cer'] = 'application/pkix-cert';
$fileDirHandler_mimeT['cgm'] = 'image/cgm';
$fileDirHandler_mimeT['chat'] = 'application/x-chat';
$fileDirHandler_mimeT['chm'] = 'application/vnd.ms-htmlhelp';
$fileDirHandler_mimeT['chrt'] = 'application/vnd.kde.kchart';
$fileDirHandler_mimeT['cif'] = 'chemical/x-cif';
$fileDirHandler_mimeT['cii'] = 'application/vnd.anser-web-certificate-issue-initiation';
$fileDirHandler_mimeT['cil'] = 'application/vnd.ms-artgalry';
$fileDirHandler_mimeT['cla'] = 'application/vnd.claymore';
$fileDirHandler_mimeT['class'] = 'application/java-vm';
$fileDirHandler_mimeT['clkk'] = 'application/vnd.crick.clicker.keyboard';
$fileDirHandler_mimeT['clkp'] = 'application/vnd.crick.clicker.palette';
$fileDirHandler_mimeT['clkt'] = 'application/vnd.crick.clicker.template';
$fileDirHandler_mimeT['clkw'] = 'application/vnd.crick.clicker.wordbank';
$fileDirHandler_mimeT['clkx'] = 'application/vnd.crick.clicker';
$fileDirHandler_mimeT['clp'] = 'application/x-msclip';
$fileDirHandler_mimeT['cmc'] = 'application/vnd.cosmocaller';
$fileDirHandler_mimeT['cmdf'] = 'chemical/x-cmdf';
$fileDirHandler_mimeT['cml'] = 'chemical/x-cml';
$fileDirHandler_mimeT['cmp'] = 'application/vnd.yellowriver-custom-menu';
$fileDirHandler_mimeT['cmx'] = 'image/x-cmx';
$fileDirHandler_mimeT['cod'] = 'application/vnd.rim.cod';
$fileDirHandler_mimeT['cpio'] = 'application/x-cpio';
$fileDirHandler_mimeT['cpt'] = 'application/mac-compactpro';
$fileDirHandler_mimeT['crd'] = 'application/x-mscardfile';
$fileDirHandler_mimeT['crl'] = 'application/pkix-crl';
$fileDirHandler_mimeT['cryptonote'] = 'application/vnd.rig.cryptonote';
$fileDirHandler_mimeT['csh'] = 'application/x-csh';
$fileDirHandler_mimeT['csml'] = 'chemical/x-csml';
$fileDirHandler_mimeT['csp'] = 'application/vnd.commonspace';
$fileDirHandler_mimeT['css'] = 'text/css';
$fileDirHandler_mimeT['csv'] = 'text/csv';
$fileDirHandler_mimeT['cu'] = 'application/cu-seeme';
$fileDirHandler_mimeT['curl'] = 'text/vnd.curl';
$fileDirHandler_mimeT['cww'] = 'application/prs.cww';
$fileDirHandler_mimeT['dae'] = 'model/vnd.collada+xml';
$fileDirHandler_mimeT['daf'] = 'application/vnd.mobius.daf';
$fileDirHandler_mimeT['davmount'] = 'application/davmount+xml';
$fileDirHandler_mimeT['dcurl'] = 'text/vnd.curl.dcurl';
$fileDirHandler_mimeT['dd2'] = 'application/vnd.oma.dd2+xml';
$fileDirHandler_mimeT['ddd'] = 'application/vnd.fujixerox.ddd';
$fileDirHandler_mimeT['deb'] = 'application/x-debian-package';
$fileDirHandler_mimeT['der'] = 'application/x-x509-ca-cert';
$fileDirHandler_mimeT['dfac'] = 'application/vnd.dreamfactory';
$fileDirHandler_mimeT['dir'] = 'application/x-director';
$fileDirHandler_mimeT['dis'] = 'application/vnd.mobius.dis';
$fileDirHandler_mimeT['djvu'] = 'image/vnd.djvu';
$fileDirHandler_mimeT['dna'] = 'application/vnd.dna';
$fileDirHandler_mimeT['doc'] = 'application/msword';
$fileDirHandler_mimeT['docm'] = 'application/vnd.ms-word.document.macroenabled.12';
$fileDirHandler_mimeT['docx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
$fileDirHandler_mimeT['dotm'] = 'application/vnd.ms-word.template.macroenabled.12';
$fileDirHandler_mimeT['dotx'] = 'application/vnd.openxmlformats-officedocument.wordprocessingml.template';
$fileDirHandler_mimeT['dp'] = 'application/vnd.osgi.dp';
$fileDirHandler_mimeT['dpg'] = 'application/vnd.dpgraph';
$fileDirHandler_mimeT['dra'] = 'audio/vnd.dra';
$fileDirHandler_mimeT['dsc'] = 'text/prs.lines.tag';
$fileDirHandler_mimeT['dssc'] = 'application/dssc+der';
$fileDirHandler_mimeT['dtb'] = 'application/x-dtbook+xml';
$fileDirHandler_mimeT['dtd'] = 'application/xml-dtd';
$fileDirHandler_mimeT['dts'] = 'audio/vnd.dts';
$fileDirHandler_mimeT['dtshd'] = 'audio/vnd.dts.hd';
$fileDirHandler_mimeT['dvi'] = 'application/x-dvi';
$fileDirHandler_mimeT['dwf'] = 'model/vnd.dwf';
$fileDirHandler_mimeT['dwg'] = 'image/vnd.dwg';
$fileDirHandler_mimeT['dxf'] = 'image/vnd.dxf';
$fileDirHandler_mimeT['dxp'] = 'application/vnd.spotfire.dxp';
$fileDirHandler_mimeT['ecelp4800'] = 'audio/vnd.nuera.ecelp4800';
$fileDirHandler_mimeT['ecelp7470'] = 'audio/vnd.nuera.ecelp7470';
$fileDirHandler_mimeT['ecelp9600'] = 'audio/vnd.nuera.ecelp9600';
$fileDirHandler_mimeT['edm'] = 'application/vnd.novadigm.edm';
$fileDirHandler_mimeT['edx'] = 'application/vnd.novadigm.edx';
$fileDirHandler_mimeT['efif'] = 'application/vnd.picsel';
$fileDirHandler_mimeT['ei6'] = 'application/vnd.pg.osasli';
$fileDirHandler_mimeT['eml'] = 'message/rfc822';
$fileDirHandler_mimeT['emma'] = 'application/emma+xml';
$fileDirHandler_mimeT['eol'] = 'audio/vnd.digital-winds';
$fileDirHandler_mimeT['eot'] = 'application/vnd.ms-fontobject';
$fileDirHandler_mimeT['epub'] = 'application/epub+zip';
$fileDirHandler_mimeT['es'] = 'application/ecmascript';
$fileDirHandler_mimeT['es3'] = 'application/vnd.eszigno3+xml';
$fileDirHandler_mimeT['esf'] = 'application/vnd.epson.esf';
$fileDirHandler_mimeT['etx'] = 'text/x-setext';
$fileDirHandler_mimeT['exe'] = 'application/x-msdownload';
$fileDirHandler_mimeT['exi'] = 'application/exi';
$fileDirHandler_mimeT['ext'] = 'application/vnd.novadigm.ext';
$fileDirHandler_mimeT['ez2'] = 'application/vnd.ezpix-album';
$fileDirHandler_mimeT['ez3'] = 'application/vnd.ezpix-package';
$fileDirHandler_mimeT['f'] = 'text/x-fortran';
$fileDirHandler_mimeT['f4v'] = 'video/x-f4v';
$fileDirHandler_mimeT['fbs'] = 'image/vnd.fastbidsheet';
$fileDirHandler_mimeT['fcs'] = 'application/vnd.isac.fcs';
$fileDirHandler_mimeT['fdf'] = 'application/vnd.fdf';
$fileDirHandler_mimeT['fe_launch'] = 'application/vnd.denovo.fcselayout-link';
$fileDirHandler_mimeT['fg5'] = 'application/vnd.fujitsu.oasysgp';
$fileDirHandler_mimeT['fh'] = 'image/x-freehand';
$fileDirHandler_mimeT['fig'] = 'application/x-xfig';
$fileDirHandler_mimeT['fli'] = 'video/x-fli';
$fileDirHandler_mimeT['flo'] = 'application/vnd.micrografx.flo';
$fileDirHandler_mimeT['flv'] = 'video/x-flv';
$fileDirHandler_mimeT['flw'] = 'application/vnd.kde.kivio';
$fileDirHandler_mimeT['flx'] = 'text/vnd.fmi.flexstor';
$fileDirHandler_mimeT['fly'] = 'text/vnd.fly';
$fileDirHandler_mimeT['fm'] = 'application/vnd.framemaker';
$fileDirHandler_mimeT['fnc'] = 'application/vnd.frogans.fnc';
$fileDirHandler_mimeT['fpx'] = 'image/vnd.fpx';
$fileDirHandler_mimeT['fsc'] = 'application/vnd.fsc.weblaunch';
$fileDirHandler_mimeT['fst'] = 'image/vnd.fst';
$fileDirHandler_mimeT['ftc'] = 'application/vnd.fluxtime.clip';
$fileDirHandler_mimeT['fti'] = 'application/vnd.anser-web-funds-transfer-initiation';
$fileDirHandler_mimeT['fvt'] = 'video/vnd.fvt';
$fileDirHandler_mimeT['fxp'] = 'application/vnd.adobe.fxp';
$fileDirHandler_mimeT['fzs'] = 'application/vnd.fuzzysheet';
$fileDirHandler_mimeT['g2w'] = 'application/vnd.geoplan';
$fileDirHandler_mimeT['g3'] = 'image/g3fax';
$fileDirHandler_mimeT['g3w'] = 'application/vnd.geospace';
$fileDirHandler_mimeT['gac'] = 'application/vnd.groove-account';
$fileDirHandler_mimeT['gdl'] = 'model/vnd.gdl';
$fileDirHandler_mimeT['geo'] = 'application/vnd.dynageo';
$fileDirHandler_mimeT['gex'] = 'application/vnd.geometry-explorer';
$fileDirHandler_mimeT['ggb'] = 'application/vnd.geogebra.file';
$fileDirHandler_mimeT['ggt'] = 'application/vnd.geogebra.tool';
$fileDirHandler_mimeT['ghf'] = 'application/vnd.groove-help';
$fileDirHandler_mimeT['gif'] = 'image/gif';
$fileDirHandler_mimeT['gim'] = 'application/vnd.groove-identity-message';
$fileDirHandler_mimeT['gmx'] = 'application/vnd.gmx';
$fileDirHandler_mimeT['gnumeric'] = 'application/x-gnumeric';
$fileDirHandler_mimeT['gph'] = 'application/vnd.flographit';
$fileDirHandler_mimeT['gqf'] = 'application/vnd.grafeq';
$fileDirHandler_mimeT['gram'] = 'application/srgs';
$fileDirHandler_mimeT['grv'] = 'application/vnd.groove-injector';
$fileDirHandler_mimeT['grxml'] = 'application/srgs+xml';
$fileDirHandler_mimeT['gsf'] = 'application/x-font-ghostscript';
$fileDirHandler_mimeT['gtar'] = 'application/x-gtar';
$fileDirHandler_mimeT['gtm'] = 'application/vnd.groove-tool-message';
$fileDirHandler_mimeT['gtw'] = 'model/vnd.gtw';
$fileDirHandler_mimeT['gv'] = 'text/vnd.graphviz';
$fileDirHandler_mimeT['gxt'] = 'application/vnd.geonext';
$fileDirHandler_mimeT['h261'] = 'video/h261';
$fileDirHandler_mimeT['h263'] = 'video/h263';
$fileDirHandler_mimeT['h264'] = 'video/h264';
$fileDirHandler_mimeT['hal'] = 'application/vnd.hal+xml';
$fileDirHandler_mimeT['hbci'] = 'application/vnd.hbci';
$fileDirHandler_mimeT['hdf'] = 'application/x-hdf';
$fileDirHandler_mimeT['hlp'] = 'application/winhlp';
$fileDirHandler_mimeT['hpgl'] = 'application/vnd.hp-hpgl';
$fileDirHandler_mimeT['hpid'] = 'application/vnd.hp-hpid';
$fileDirHandler_mimeT['hps'] = 'application/vnd.hp-hps';
$fileDirHandler_mimeT['hqx'] = 'application/mac-binhex40';
$fileDirHandler_mimeT['htke'] = 'application/vnd.kenameaapp';
$fileDirHandler_mimeT['html'] = 'text/html';
$fileDirHandler_mimeT['hvd'] = 'application/vnd.yamaha.hv-dic';
$fileDirHandler_mimeT['hvp'] = 'application/vnd.yamaha.hv-voice';
$fileDirHandler_mimeT['hvs'] = 'application/vnd.yamaha.hv-script';
$fileDirHandler_mimeT['i2g'] = 'application/vnd.intergeo';
$fileDirHandler_mimeT['icc'] = 'application/vnd.iccprofile';
$fileDirHandler_mimeT['ice'] = 'x-conference/x-cooltalk';
$fileDirHandler_mimeT['ico'] = 'image/x-icon';
$fileDirHandler_mimeT['ics'] = 'text/calendar';
$fileDirHandler_mimeT['ief'] = 'image/ief';
$fileDirHandler_mimeT['ifm'] = 'application/vnd.shana.informed.formdata';
$fileDirHandler_mimeT['igl'] = 'application/vnd.igloader';
$fileDirHandler_mimeT['igm'] = 'application/vnd.insors.igm';
$fileDirHandler_mimeT['igs'] = 'model/iges';
$fileDirHandler_mimeT['igx'] = 'application/vnd.micrografx.igx';
$fileDirHandler_mimeT['iif'] = 'application/vnd.shana.informed.interchange';
$fileDirHandler_mimeT['imp'] = 'application/vnd.accpac.simply.imp';
$fileDirHandler_mimeT['ims'] = 'application/vnd.ms-ims';
$fileDirHandler_mimeT['ipfix'] = 'application/ipfix';
$fileDirHandler_mimeT['ipk'] = 'application/vnd.shana.informed.package';
$fileDirHandler_mimeT['irm'] = 'application/vnd.ibm.rights-management';
$fileDirHandler_mimeT['irp'] = 'application/vnd.irepository.package+xml';
$fileDirHandler_mimeT['itp'] = 'application/vnd.shana.informed.formtemplate';
$fileDirHandler_mimeT['ivp'] = 'application/vnd.immervision-ivp';
$fileDirHandler_mimeT['ivu'] = 'application/vnd.immervision-ivu';
$fileDirHandler_mimeT['jad'] = 'text/vnd.sun.j2me.app-descriptor';
$fileDirHandler_mimeT['jam'] = 'application/vnd.jam';
$fileDirHandler_mimeT['jar'] = 'application/java-archive';
$fileDirHandler_mimeT['java'] = 'text/x-java-source,java';
$fileDirHandler_mimeT['jisp'] = 'application/vnd.jisp';
$fileDirHandler_mimeT['jlt'] = 'application/vnd.hp-jlyt';
$fileDirHandler_mimeT['jnlp'] = 'application/x-java-jnlp-file';
$fileDirHandler_mimeT['joda'] = 'application/vnd.joost.joda-archive';
$fileDirHandler_mimeT['jpg'] = 'image/jpeg';
$fileDirHandler_mimeT['jpeg'] = 'image/jpeg';
$fileDirHandler_mimeT['jpgv'] = 'video/jpeg';
$fileDirHandler_mimeT['jpm'] = 'video/jpm';
$fileDirHandler_mimeT['js'] = 'application/javascript';
$fileDirHandler_mimeT['json'] = 'application/json';
$fileDirHandler_mimeT['karbon'] = 'application/vnd.kde.karbon';
$fileDirHandler_mimeT['kfo'] = 'application/vnd.kde.kformula';
$fileDirHandler_mimeT['kia'] = 'application/vnd.kidspiration';
$fileDirHandler_mimeT['kml'] = 'application/vnd.google-earth.kml+xml';
$fileDirHandler_mimeT['kmz'] = 'application/vnd.google-earth.kmz';
$fileDirHandler_mimeT['kne'] = 'application/vnd.kinar';
$fileDirHandler_mimeT['kon'] = 'application/vnd.kde.kontour';
$fileDirHandler_mimeT['kpr'] = 'application/vnd.kde.kpresenter';
$fileDirHandler_mimeT['ksp'] = 'application/vnd.kde.kspread';
$fileDirHandler_mimeT['ktx'] = 'image/ktx';
$fileDirHandler_mimeT['ktz'] = 'application/vnd.kahootz';
$fileDirHandler_mimeT['kwd'] = 'application/vnd.kde.kword';
$fileDirHandler_mimeT['lasxml'] = 'application/vnd.las.las+xml';
$fileDirHandler_mimeT['latex'] = 'application/x-latex';
$fileDirHandler_mimeT['lbd'] = 'application/vnd.llamagraphics.life-balance.desktop';
$fileDirHandler_mimeT['lbe'] = 'application/vnd.llamagraphics.life-balance.exchange+xml';
$fileDirHandler_mimeT['les'] = 'application/vnd.hhe.lesson-player';
$fileDirHandler_mimeT['link66'] = 'application/vnd.route66.link66+xml';
$fileDirHandler_mimeT['lrm'] = 'application/vnd.ms-lrm';
$fileDirHandler_mimeT['ltf'] = 'application/vnd.frogans.ltf';
$fileDirHandler_mimeT['lvp'] = 'audio/vnd.lucent.voice';
$fileDirHandler_mimeT['lwp'] = 'application/vnd.lotus-wordpro';
$fileDirHandler_mimeT['m21'] = 'application/mp21';
$fileDirHandler_mimeT['m3u'] = 'audio/x-mpegurl';
$fileDirHandler_mimeT['m3u8'] = 'application/vnd.apple.mpegurl';
$fileDirHandler_mimeT['m4v'] = 'video/x-m4v';
$fileDirHandler_mimeT['ma'] = 'application/mathematica';
$fileDirHandler_mimeT['mads'] = 'application/mads+xml';
$fileDirHandler_mimeT['mag'] = 'application/vnd.ecowin.chart';
$fileDirHandler_mimeT['mathml'] = 'application/mathml+xml';
$fileDirHandler_mimeT['mbk'] = 'application/vnd.mobius.mbk';
$fileDirHandler_mimeT['mbox'] = 'application/mbox';
$fileDirHandler_mimeT['mc1'] = 'application/vnd.medcalcdata';
$fileDirHandler_mimeT['mcd'] = 'application/vnd.mcd';
$fileDirHandler_mimeT['mcurl'] = 'text/vnd.curl.mcurl';
$fileDirHandler_mimeT['mdb'] = 'application/x-msaccess';
$fileDirHandler_mimeT['mdi'] = 'image/vnd.ms-modi';
$fileDirHandler_mimeT['meta4'] = 'application/metalink4+xml';
$fileDirHandler_mimeT['mets'] = 'application/mets+xml';
$fileDirHandler_mimeT['mfm'] = 'application/vnd.mfmp';
$fileDirHandler_mimeT['mgp'] = 'application/vnd.osgeo.mapguide.package';
$fileDirHandler_mimeT['mgz'] = 'application/vnd.proteus.magazine';
$fileDirHandler_mimeT['mid'] = 'audio/midi';
$fileDirHandler_mimeT['mif'] = 'application/vnd.mif';
$fileDirHandler_mimeT['mj2'] = 'video/mj2';
$fileDirHandler_mimeT['mlp'] = 'application/vnd.dolby.mlp';
$fileDirHandler_mimeT['mmd'] = 'application/vnd.chipnuts.karaoke-mmd';
$fileDirHandler_mimeT['mmf'] = 'application/vnd.smaf';
$fileDirHandler_mimeT['mmr'] = 'image/vnd.fujixerox.edmics-mmr';
$fileDirHandler_mimeT['mny'] = 'application/x-msmoney';
$fileDirHandler_mimeT['mods'] = 'application/mods+xml';
$fileDirHandler_mimeT['movie'] = 'video/x-sgi-movie';
$fileDirHandler_mimeT['mp3'] = 'audio/mpeg';
$fileDirHandler_mimeT['mp4'] = 'video/mp4';
$fileDirHandler_mimeT['mp4a'] = 'audio/mp4';
$fileDirHandler_mimeT['m4a'] = 'audio/mp4';
$fileDirHandler_mimeT['mpc'] = 'application/vnd.mophun.certificate';
$fileDirHandler_mimeT['mpeg'] = 'video/mpeg';
$fileDirHandler_mimeT['mpg'] = 'video/mpeg';
$fileDirHandler_mimeT['mpga'] = 'audio/mpeg';
$fileDirHandler_mimeT['mpkg'] = 'application/vnd.apple.installer+xml';
$fileDirHandler_mimeT['mpm'] = 'application/vnd.blueice.multipass';
$fileDirHandler_mimeT['mpn'] = 'application/vnd.mophun.application';
$fileDirHandler_mimeT['mpp'] = 'application/vnd.ms-project';
$fileDirHandler_mimeT['mpy'] = 'application/vnd.ibm.minipay';
$fileDirHandler_mimeT['mqy'] = 'application/vnd.mobius.mqy';
$fileDirHandler_mimeT['mrc'] = 'application/marc';
$fileDirHandler_mimeT['mrcx'] = 'application/marcxml+xml';
$fileDirHandler_mimeT['mscml'] = 'application/mediaservercontrol+xml';
$fileDirHandler_mimeT['mseq'] = 'application/vnd.mseq';
$fileDirHandler_mimeT['msf'] = 'application/vnd.epson.msf';
$fileDirHandler_mimeT['msh'] = 'model/mesh';
$fileDirHandler_mimeT['msl'] = 'application/vnd.mobius.msl';
$fileDirHandler_mimeT['msty'] = 'application/vnd.muvee.style';
$fileDirHandler_mimeT['mts'] = 'model/vnd.mts';
$fileDirHandler_mimeT['mus'] = 'application/vnd.musician';
$fileDirHandler_mimeT['musicxml'] = 'application/vnd.recordare.musicxml+xml';
$fileDirHandler_mimeT['mvb'] = 'application/x-msmediaview';
$fileDirHandler_mimeT['mwf'] = 'application/vnd.mfer';
$fileDirHandler_mimeT['mxf'] = 'application/mxf';
$fileDirHandler_mimeT['mxl'] = 'application/vnd.recordare.musicxml';
$fileDirHandler_mimeT['mxml'] = 'application/xv+xml';
$fileDirHandler_mimeT['mxs'] = 'application/vnd.triscape.mxs';
$fileDirHandler_mimeT['mxu'] = 'video/vnd.mpegurl';
$fileDirHandler_mimeT['N/A'] = 'application/andrew-inset';
$fileDirHandler_mimeT['n3'] = 'text/n3';
$fileDirHandler_mimeT['nbp'] = 'application/vnd.wolfram.player';
$fileDirHandler_mimeT['nc'] = 'application/x-netcdf';
$fileDirHandler_mimeT['ncx'] = 'application/x-dtbncx+xml';
$fileDirHandler_mimeT['n-gage'] = 'application/vnd.nokia.n-gage.symbian.install';
$fileDirHandler_mimeT['ngdat'] = 'application/vnd.nokia.n-gage.data';
$fileDirHandler_mimeT['nlu'] = 'application/vnd.neurolanguage.nlu';
$fileDirHandler_mimeT['nml'] = 'application/vnd.enliven';
$fileDirHandler_mimeT['nnd'] = 'application/vnd.noblenet-directory';
$fileDirHandler_mimeT['nns'] = 'application/vnd.noblenet-sealer';
$fileDirHandler_mimeT['nnw'] = 'application/vnd.noblenet-web';
$fileDirHandler_mimeT['npx'] = 'image/vnd.net-fpx';
$fileDirHandler_mimeT['nsf'] = 'application/vnd.lotus-notes';
$fileDirHandler_mimeT['oa2'] = 'application/vnd.fujitsu.oasys2';
$fileDirHandler_mimeT['oa3'] = 'application/vnd.fujitsu.oasys3';
$fileDirHandler_mimeT['oas'] = 'application/vnd.fujitsu.oasys';
$fileDirHandler_mimeT['obd'] = 'application/x-msbinder';
$fileDirHandler_mimeT['oda'] = 'application/oda';
$fileDirHandler_mimeT['odb'] = 'application/vnd.oasis.opendocument.database';
$fileDirHandler_mimeT['odc'] = 'application/vnd.oasis.opendocument.chart';
$fileDirHandler_mimeT['odf'] = 'application/vnd.oasis.opendocument.formula';
$fileDirHandler_mimeT['odft'] = 'application/vnd.oasis.opendocument.formula-template';
$fileDirHandler_mimeT['odg'] = 'application/vnd.oasis.opendocument.graphics';
$fileDirHandler_mimeT['odi'] = 'application/vnd.oasis.opendocument.image';
$fileDirHandler_mimeT['odm'] = 'application/vnd.oasis.opendocument.text-master';
$fileDirHandler_mimeT['odp'] = 'application/vnd.oasis.opendocument.presentation';
$fileDirHandler_mimeT['ods'] = 'application/vnd.oasis.opendocument.spreadsheet';
$fileDirHandler_mimeT['odt'] = 'application/vnd.oasis.opendocument.text';
$fileDirHandler_mimeT['oga'] = 'audio/ogg';
$fileDirHandler_mimeT['ogg'] = 'video/ogg';
$fileDirHandler_mimeT['ogga'] = 'audio/ogg';
$fileDirHandler_mimeT['ogv'] = 'video/ogg';
$fileDirHandler_mimeT['ogx'] = 'application/ogg';
$fileDirHandler_mimeT['onetoc'] = 'application/onenote';
$fileDirHandler_mimeT['opf'] = 'application/oebps-package+xml';
$fileDirHandler_mimeT['org'] = 'application/vnd.lotus-organizer';
$fileDirHandler_mimeT['osf'] = 'application/vnd.yamaha.openscoreformat';
$fileDirHandler_mimeT['osfpvg'] = 'application/vnd.yamaha.openscoreformat.osfpvg+xml';
$fileDirHandler_mimeT['otc'] = 'application/vnd.oasis.opendocument.chart-template';
$fileDirHandler_mimeT['otf'] = 'application/x-font-otf';
$fileDirHandler_mimeT['otg'] = 'application/vnd.oasis.opendocument.graphics-template';
$fileDirHandler_mimeT['oth'] = 'application/vnd.oasis.opendocument.text-web';
$fileDirHandler_mimeT['oti'] = 'application/vnd.oasis.opendocument.image-template';
$fileDirHandler_mimeT['otp'] = 'application/vnd.oasis.opendocument.presentation-template';
$fileDirHandler_mimeT['ots'] = 'application/vnd.oasis.opendocument.spreadsheet-template';
$fileDirHandler_mimeT['ott'] = 'application/vnd.oasis.opendocument.text-template';
$fileDirHandler_mimeT['oxt'] = 'application/vnd.openofficeorg.extension';
$fileDirHandler_mimeT['p'] = 'text/x-pascal';
$fileDirHandler_mimeT['p10'] = 'application/pkcs10';
$fileDirHandler_mimeT['p12'] = 'application/x-pkcs12';
$fileDirHandler_mimeT['p7b'] = 'application/x-pkcs7-certificates';
$fileDirHandler_mimeT['p7m'] = 'application/pkcs7-mime';
$fileDirHandler_mimeT['p7r'] = 'application/x-pkcs7-certreqresp';
$fileDirHandler_mimeT['p7s'] = 'application/pkcs7-signature';
$fileDirHandler_mimeT['p8'] = 'application/pkcs8';
$fileDirHandler_mimeT['par'] = 'text/plain-bas';
$fileDirHandler_mimeT['paw'] = 'application/vnd.pawaafile';
$fileDirHandler_mimeT['pbd'] = 'application/vnd.powerbuilder6';
$fileDirHandler_mimeT['pbm'] = 'image/x-portable-bitmap';
$fileDirHandler_mimeT['pcf'] = 'application/x-font-pcf';
$fileDirHandler_mimeT['pcl'] = 'application/vnd.hp-pcl';
$fileDirHandler_mimeT['pclxl'] = 'application/vnd.hp-pclxl';
$fileDirHandler_mimeT['pcurl'] = 'application/vnd.curl.pcurl';
$fileDirHandler_mimeT['pcx'] = 'image/x-pcx';
$fileDirHandler_mimeT['pdb'] = 'application/vnd.palm';
$fileDirHandler_mimeT['pdf'] = 'application/pdf';
$fileDirHandler_mimeT['pfa'] = 'application/x-font-type1';
$fileDirHandler_mimeT['pfr'] = 'application/font-tdpfr';
$fileDirHandler_mimeT['pgm'] = 'image/x-portable-graymap';
$fileDirHandler_mimeT['pgn'] = 'application/x-chess-pgn';
$fileDirHandler_mimeT['pgp'] = 'application/pgp-signature';
$fileDirHandler_mimeT['pic'] = 'image/x-pict';
$fileDirHandler_mimeT['pki'] = 'application/pkixcmp';
$fileDirHandler_mimeT['pkipath'] = 'application/pkix-pkipath';
$fileDirHandler_mimeT['plb'] = 'application/vnd.3gpp.pic-bw-large';
$fileDirHandler_mimeT['plc'] = 'application/vnd.mobius.plc';
$fileDirHandler_mimeT['plf'] = 'application/vnd.pocketlearn';
$fileDirHandler_mimeT['pls'] = 'application/pls+xml';
$fileDirHandler_mimeT['pml'] = 'application/vnd.ctc-posml';
$fileDirHandler_mimeT['png'] = 'image/png';
$fileDirHandler_mimeT['pnm'] = 'image/x-portable-anymap';
$fileDirHandler_mimeT['portpkg'] = 'application/vnd.macports.portpkg';
$fileDirHandler_mimeT['potm'] = 'application/vnd.ms-powerpoint.template.macroenabled.12';
$fileDirHandler_mimeT['potx'] = 'application/vnd.openxmlformats-officedocument.presentationml.template';
$fileDirHandler_mimeT['ppam'] = 'application/vnd.ms-powerpoint.addin.macroenabled.12';
$fileDirHandler_mimeT['ppd'] = 'application/vnd.cups-ppd';
$fileDirHandler_mimeT['ppm'] = 'image/x-portable-pixmap';
$fileDirHandler_mimeT['ppsm'] = 'application/vnd.ms-powerpoint.slideshow.macroenabled.12';
$fileDirHandler_mimeT['ppsx'] = 'application/vnd.openxmlformats-officedocument.presentationml.slideshow';
$fileDirHandler_mimeT['ppt'] = 'application/vnd.ms-powerpoint';
$fileDirHandler_mimeT['pptm'] = 'application/vnd.ms-powerpoint.presentation.macroenabled.12';
$fileDirHandler_mimeT['pptx'] = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
$fileDirHandler_mimeT['prc'] = 'application/x-mobipocket-ebook';
$fileDirHandler_mimeT['pre'] = 'application/vnd.lotus-freelance';
$fileDirHandler_mimeT['prf'] = 'application/pics-rules';
$fileDirHandler_mimeT['psb'] = 'application/vnd.3gpp.pic-bw-small';
$fileDirHandler_mimeT['psd'] = 'image/vnd.adobe.photoshop';
$fileDirHandler_mimeT['psf'] = 'application/x-font-linux-psf';
$fileDirHandler_mimeT['pskcxml'] = 'application/pskc+xml';
$fileDirHandler_mimeT['ptid'] = 'application/vnd.pvi.ptid1';
$fileDirHandler_mimeT['pub'] = 'application/x-mspublisher';
$fileDirHandler_mimeT['pvb'] = 'application/vnd.3gpp.pic-bw-var';
$fileDirHandler_mimeT['pwn'] = 'application/vnd.3m.post-it-notes';
$fileDirHandler_mimeT['pya'] = 'audio/vnd.ms-playready.media.pya';
$fileDirHandler_mimeT['pyv'] = 'video/vnd.ms-playready.media.pyv';
$fileDirHandler_mimeT['qam'] = 'application/vnd.epson.quickanime';
$fileDirHandler_mimeT['qbo'] = 'application/vnd.intu.qbo';
$fileDirHandler_mimeT['qfx'] = 'application/vnd.intu.qfx';
$fileDirHandler_mimeT['qps'] = 'application/vnd.publishare-delta-tree';
$fileDirHandler_mimeT['qt'] = 'video/quicktime';
$fileDirHandler_mimeT['qxd'] = 'application/vnd.quark.quarkxpress';
$fileDirHandler_mimeT['ram'] = 'audio/x-pn-realaudio';
$fileDirHandler_mimeT['rar'] = 'application/x-rar-compressed';
$fileDirHandler_mimeT['ras'] = 'image/x-cmu-raster';
$fileDirHandler_mimeT['rcprofile'] = 'application/vnd.ipunplugged.rcprofile';
$fileDirHandler_mimeT['rdf'] = 'application/rdf+xml';
$fileDirHandler_mimeT['rdz'] = 'application/vnd.data-vision.rdz';
$fileDirHandler_mimeT['rep'] = 'application/vnd.businessobjects';
$fileDirHandler_mimeT['res'] = 'application/x-dtbresource+xml';
$fileDirHandler_mimeT['rgb'] = 'image/x-rgb';
$fileDirHandler_mimeT['rif'] = 'application/reginfo+xml';
$fileDirHandler_mimeT['rip'] = 'audio/vnd.rip';
$fileDirHandler_mimeT['rl'] = 'application/resource-lists+xml';
$fileDirHandler_mimeT['rlc'] = 'image/vnd.fujixerox.edmics-rlc';
$fileDirHandler_mimeT['rld'] = 'application/resource-lists-diff+xml';
$fileDirHandler_mimeT['rm'] = 'application/vnd.rn-realmedia';
$fileDirHandler_mimeT['rmp'] = 'audio/x-pn-realaudio-plugin';
$fileDirHandler_mimeT['rms'] = 'application/vnd.jcp.javame.midlet-rms';
$fileDirHandler_mimeT['rnc'] = 'application/relax-ng-compact-syntax';
$fileDirHandler_mimeT['rp9'] = 'application/vnd.cloanto.rp9';
$fileDirHandler_mimeT['rpss'] = 'application/vnd.nokia.radio-presets';
$fileDirHandler_mimeT['rpst'] = 'application/vnd.nokia.radio-preset';
$fileDirHandler_mimeT['rq'] = 'application/sparql-query';
$fileDirHandler_mimeT['rs'] = 'application/rls-services+xml';
$fileDirHandler_mimeT['rsd'] = 'application/rsd+xml';
$fileDirHandler_mimeT['rss'] = 'application/rss+xml';
$fileDirHandler_mimeT['rtf'] = 'application/rtf';
$fileDirHandler_mimeT['rtx'] = 'text/richtext';
$fileDirHandler_mimeT['s'] = 'text/x-asm';
$fileDirHandler_mimeT['saf'] = 'application/vnd.yamaha.smaf-audio';
$fileDirHandler_mimeT['sbml'] = 'application/sbml+xml';
$fileDirHandler_mimeT['sc'] = 'application/vnd.ibm.secure-container';
$fileDirHandler_mimeT['scd'] = 'application/x-msschedule';
$fileDirHandler_mimeT['scm'] = 'application/vnd.lotus-screencam';
$fileDirHandler_mimeT['scq'] = 'application/scvp-cv-request';
$fileDirHandler_mimeT['scs'] = 'application/scvp-cv-response';
$fileDirHandler_mimeT['scurl'] = 'text/vnd.curl.scurl';
$fileDirHandler_mimeT['sda'] = 'application/vnd.stardivision.draw';
$fileDirHandler_mimeT['sdc'] = 'application/vnd.stardivision.calc';
$fileDirHandler_mimeT['sdd'] = 'application/vnd.stardivision.impress';
$fileDirHandler_mimeT['sdkm'] = 'application/vnd.solent.sdkm+xml';
$fileDirHandler_mimeT['sdp'] = 'application/sdp';
$fileDirHandler_mimeT['sdw'] = 'application/vnd.stardivision.writer';
$fileDirHandler_mimeT['see'] = 'application/vnd.seemail';
$fileDirHandler_mimeT['seed'] = 'application/vnd.fdsn.seed';
$fileDirHandler_mimeT['sema'] = 'application/vnd.sema';
$fileDirHandler_mimeT['semd'] = 'application/vnd.semd';
$fileDirHandler_mimeT['semf'] = 'application/vnd.semf';
$fileDirHandler_mimeT['ser'] = 'application/java-serialized-object';
$fileDirHandler_mimeT['setpay'] = 'application/set-payment-initiation';
$fileDirHandler_mimeT['setreg'] = 'application/set-registration-initiation';
$fileDirHandler_mimeT['sfd-hdstx'] = 'application/vnd.hydrostatix.sof-data';
$fileDirHandler_mimeT['sfs'] = 'application/vnd.spotfire.sfs';
$fileDirHandler_mimeT['sgl'] = 'application/vnd.stardivision.writer-global';
$fileDirHandler_mimeT['sgml'] = 'text/sgml';
$fileDirHandler_mimeT['sh'] = 'application/x-sh';
$fileDirHandler_mimeT['shar'] = 'application/x-shar';
$fileDirHandler_mimeT['shf'] = 'application/shf+xml';
$fileDirHandler_mimeT['sis'] = 'application/vnd.symbian.install';
$fileDirHandler_mimeT['sit'] = 'application/x-stuffit';
$fileDirHandler_mimeT['sitx'] = 'application/x-stuffitx';
$fileDirHandler_mimeT['skp'] = 'application/vnd.koan';
$fileDirHandler_mimeT['sldm'] = 'application/vnd.ms-powerpoint.slide.macroenabled.12';
$fileDirHandler_mimeT['sldx'] = 'application/vnd.openxmlformats-officedocument.presentationml.slide';
$fileDirHandler_mimeT['slt'] = 'application/vnd.epson.salt';
$fileDirHandler_mimeT['sm'] = 'application/vnd.stepmania.stepchart';
$fileDirHandler_mimeT['smf'] = 'application/vnd.stardivision.math';
$fileDirHandler_mimeT['smi'] = 'application/smil+xml';
$fileDirHandler_mimeT['snf'] = 'application/x-font-snf';
$fileDirHandler_mimeT['spf'] = 'application/vnd.yamaha.smaf-phrase';
$fileDirHandler_mimeT['spl'] = 'application/x-futuresplash';
$fileDirHandler_mimeT['spot'] = 'text/vnd.in3d.spot';
$fileDirHandler_mimeT['spp'] = 'application/scvp-vp-response';
$fileDirHandler_mimeT['spq'] = 'application/scvp-vp-request';
$fileDirHandler_mimeT['src'] = 'application/x-wais-source';
$fileDirHandler_mimeT['sru'] = 'application/sru+xml';
$fileDirHandler_mimeT['srx'] = 'application/sparql-results+xml';
$fileDirHandler_mimeT['sse'] = 'application/vnd.kodak-descriptor';
$fileDirHandler_mimeT['ssf'] = 'application/vnd.epson.ssf';
$fileDirHandler_mimeT['ssml'] = 'application/ssml+xml';
$fileDirHandler_mimeT['st'] = 'application/vnd.sailingtracker.track';
$fileDirHandler_mimeT['stc'] = 'application/vnd.sun.xml.calc.template';
$fileDirHandler_mimeT['std'] = 'application/vnd.sun.xml.draw.template';
$fileDirHandler_mimeT['stf'] = 'application/vnd.wt.stf';
$fileDirHandler_mimeT['sti'] = 'application/vnd.sun.xml.impress.template';
$fileDirHandler_mimeT['stk'] = 'application/hyperstudio';
$fileDirHandler_mimeT['stl'] = 'application/vnd.ms-pki.stl';
$fileDirHandler_mimeT['str'] = 'application/vnd.pg.format';
$fileDirHandler_mimeT['stw'] = 'application/vnd.sun.xml.writer.template';
$fileDirHandler_mimeT['sub'] = 'image/vnd.dvb.subtitle';
$fileDirHandler_mimeT['sus'] = 'application/vnd.sus-calendar';
$fileDirHandler_mimeT['sv4cpio'] = 'application/x-sv4cpio';
$fileDirHandler_mimeT['sv4crc'] = 'application/x-sv4crc';
$fileDirHandler_mimeT['svc'] = 'application/vnd.dvb.service';
$fileDirHandler_mimeT['svd'] = 'application/vnd.svd';
$fileDirHandler_mimeT['svg'] = 'image/svg+xml';
$fileDirHandler_mimeT['swf'] = 'application/x-shockwave-flash';
$fileDirHandler_mimeT['swi'] = 'application/vnd.aristanetworks.swi';
$fileDirHandler_mimeT['sxc'] = 'application/vnd.sun.xml.calc';
$fileDirHandler_mimeT['sxd'] = 'application/vnd.sun.xml.draw';
$fileDirHandler_mimeT['sxg'] = 'application/vnd.sun.xml.writer.global';
$fileDirHandler_mimeT['sxi'] = 'application/vnd.sun.xml.impress';
$fileDirHandler_mimeT['sxm'] = 'application/vnd.sun.xml.math';
$fileDirHandler_mimeT['sxw'] = 'application/vnd.sun.xml.writer';
$fileDirHandler_mimeT['t'] = 'text/troff';
$fileDirHandler_mimeT['tao'] = 'application/vnd.tao.intent-module-archive';
$fileDirHandler_mimeT['tar'] = 'application/x-tar';
$fileDirHandler_mimeT['tcap'] = 'application/vnd.3gpp2.tcap';
$fileDirHandler_mimeT['tcl'] = 'application/x-tcl';
$fileDirHandler_mimeT['teacher'] = 'application/vnd.smart.teacher';
$fileDirHandler_mimeT['tei'] = 'application/tei+xml';
$fileDirHandler_mimeT['tex'] = 'application/x-tex';
$fileDirHandler_mimeT['texinfo'] = 'application/x-texinfo';
$fileDirHandler_mimeT['tfi'] = 'application/thraud+xml';
$fileDirHandler_mimeT['tfm'] = 'application/x-tex-tfm';
$fileDirHandler_mimeT['thmx'] = 'application/vnd.ms-officetheme';
$fileDirHandler_mimeT['tiff'] = 'image/tiff';
$fileDirHandler_mimeT['tmo'] = 'application/vnd.tmobile-livetv';
$fileDirHandler_mimeT['torrent'] = 'application/x-bittorrent';
$fileDirHandler_mimeT['tpl'] = 'application/vnd.groove-tool-template';
$fileDirHandler_mimeT['tpt'] = 'application/vnd.trid.tpt';
$fileDirHandler_mimeT['tra'] = 'application/vnd.trueapp';
$fileDirHandler_mimeT['trm'] = 'application/x-msterminal';
$fileDirHandler_mimeT['tsd'] = 'application/timestamped-data';
$fileDirHandler_mimeT['tsv'] = 'text/tab-separated-values';
$fileDirHandler_mimeT['ttf'] = 'application/x-font-ttf';
$fileDirHandler_mimeT['ttl'] = 'text/turtle';
$fileDirHandler_mimeT['twd'] = 'application/vnd.simtech-mindmapper';
$fileDirHandler_mimeT['txd'] = 'application/vnd.genomatix.tuxedo';
$fileDirHandler_mimeT['txf'] = 'application/vnd.mobius.txf';
$fileDirHandler_mimeT['txt'] = 'text/plain';
$fileDirHandler_mimeT['ufd'] = 'application/vnd.ufdl';
$fileDirHandler_mimeT['umj'] = 'application/vnd.umajin';
$fileDirHandler_mimeT['unityweb'] = 'application/vnd.unity';
$fileDirHandler_mimeT['uoml'] = 'application/vnd.uoml+xml';
$fileDirHandler_mimeT['uri'] = 'text/uri-list';
$fileDirHandler_mimeT['ustar'] = 'application/x-ustar';
$fileDirHandler_mimeT['utz'] = 'application/vnd.uiq.theme';
$fileDirHandler_mimeT['uu'] = 'text/x-uuencode';
$fileDirHandler_mimeT['uva'] = 'audio/vnd.dece.audio';
$fileDirHandler_mimeT['uvh'] = 'video/vnd.dece.hd';
$fileDirHandler_mimeT['uvi'] = 'image/vnd.dece.graphic';
$fileDirHandler_mimeT['uvm'] = 'video/vnd.dece.mobile';
$fileDirHandler_mimeT['uvp'] = 'video/vnd.dece.pd';
$fileDirHandler_mimeT['uvs'] = 'video/vnd.dece.sd';
$fileDirHandler_mimeT['uvu'] = 'video/vnd.uvvu.mp4';
$fileDirHandler_mimeT['uvv'] = 'video/vnd.dece.video';
$fileDirHandler_mimeT['vcd'] = 'application/x-cdlink';
$fileDirHandler_mimeT['vcf'] = 'text/x-vcard';
$fileDirHandler_mimeT['vcg'] = 'application/vnd.groove-vcard';
$fileDirHandler_mimeT['vcs'] = 'text/x-vcalendar';
$fileDirHandler_mimeT['vcx'] = 'application/vnd.vcx';
$fileDirHandler_mimeT['vis'] = 'application/vnd.visionary';
$fileDirHandler_mimeT['viv'] = 'video/vnd.vivo';
$fileDirHandler_mimeT['vsd'] = 'application/vnd.visio';
$fileDirHandler_mimeT['vsf'] = 'application/vnd.vsf';
$fileDirHandler_mimeT['vtu'] = 'model/vnd.vtu';
$fileDirHandler_mimeT['vxml'] = 'application/voicexml+xml';
$fileDirHandler_mimeT['wad'] = 'application/x-doom';
$fileDirHandler_mimeT['wav'] = 'audio/x-wav';
$fileDirHandler_mimeT['wax'] = 'audio/x-ms-wax';
$fileDirHandler_mimeT['wbmp'] = 'image/vnd.wap.wbmp';
$fileDirHandler_mimeT['wbs'] = 'application/vnd.criticaltools.wbs+xml';
$fileDirHandler_mimeT['wbxml'] = 'application/vnd.wap.wbxml';
$fileDirHandler_mimeT['weba'] = 'audio/webm';
$fileDirHandler_mimeT['webm'] = 'video/webm';
$fileDirHandler_mimeT['webma'] = 'audio/webm';
$fileDirHandler_mimeT['webp'] = 'image/webp';
$fileDirHandler_mimeT['wg'] = 'application/vnd.pmi.widget';
$fileDirHandler_mimeT['wgt'] = 'application/widget';
$fileDirHandler_mimeT['wm'] = 'video/x-ms-wm';
$fileDirHandler_mimeT['wma'] = 'audio/x-ms-wma';
$fileDirHandler_mimeT['wmd'] = 'application/x-ms-wmd';
$fileDirHandler_mimeT['wmf'] = 'application/x-msmetafile';
$fileDirHandler_mimeT['wml'] = 'text/vnd.wap.wml';
$fileDirHandler_mimeT['wmlc'] = 'application/vnd.wap.wmlc';
$fileDirHandler_mimeT['wmls'] = 'text/vnd.wap.wmlscript';
$fileDirHandler_mimeT['wmlsc'] = 'application/vnd.wap.wmlscriptc';
$fileDirHandler_mimeT['wmv'] = 'video/x-ms-wmv';
$fileDirHandler_mimeT['wmx'] = 'video/x-ms-wmx';
$fileDirHandler_mimeT['wmz'] = 'application/x-ms-wmz';
$fileDirHandler_mimeT['woff'] = 'application/x-font-woff';
$fileDirHandler_mimeT['wpd'] = 'application/vnd.wordperfect';
$fileDirHandler_mimeT['wpl'] = 'application/vnd.ms-wpl';
$fileDirHandler_mimeT['wps'] = 'application/vnd.ms-works';
$fileDirHandler_mimeT['wqd'] = 'application/vnd.wqd';
$fileDirHandler_mimeT['wri'] = 'application/x-mswrite';
$fileDirHandler_mimeT['wrl'] = 'model/vrml';
$fileDirHandler_mimeT['wsdl'] = 'application/wsdl+xml';
$fileDirHandler_mimeT['wspolicy'] = 'application/wspolicy+xml';
$fileDirHandler_mimeT['wtb'] = 'application/vnd.webturbo';
$fileDirHandler_mimeT['wvx'] = 'video/x-ms-wvx';
$fileDirHandler_mimeT['x3d'] = 'application/vnd.hzn-3d-crossword';
$fileDirHandler_mimeT['xap'] = 'application/x-silverlight-app';
$fileDirHandler_mimeT['xar'] = 'application/vnd.xara';
$fileDirHandler_mimeT['xbap'] = 'application/x-ms-xbap';
$fileDirHandler_mimeT['xbd'] = 'application/vnd.fujixerox.docuworks.binder';
$fileDirHandler_mimeT['xbm'] = 'image/x-xbitmap';
$fileDirHandler_mimeT['xdf'] = 'application/xcap-diff+xml';
$fileDirHandler_mimeT['xdm'] = 'application/vnd.syncml.dm+xml';
$fileDirHandler_mimeT['xdp'] = 'application/vnd.adobe.xdp+xml';
$fileDirHandler_mimeT['xdssc'] = 'application/dssc+xml';
$fileDirHandler_mimeT['xdw'] = 'application/vnd.fujixerox.docuworks';
$fileDirHandler_mimeT['xenc'] = 'application/xenc+xml';
$fileDirHandler_mimeT['xer'] = 'application/patch-ops-error+xml';
$fileDirHandler_mimeT['xfdf'] = 'application/vnd.adobe.xfdf';
$fileDirHandler_mimeT['xfdl'] = 'application/vnd.xfdl';
$fileDirHandler_mimeT['xhtml'] = 'application/xhtml+xml';
$fileDirHandler_mimeT['xif'] = 'image/vnd.xiff';
$fileDirHandler_mimeT['xlam'] = 'application/vnd.ms-excel.addin.macroenabled.12';
$fileDirHandler_mimeT['xls'] = 'application/vnd.ms-excel';
$fileDirHandler_mimeT['xlsb'] = 'application/vnd.ms-excel.sheet.binary.macroenabled.12';
$fileDirHandler_mimeT['xlsm'] = 'application/vnd.ms-excel.sheet.macroenabled.12';
$fileDirHandler_mimeT['xlsx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
$fileDirHandler_mimeT['xltm'] = 'application/vnd.ms-excel.template.macroenabled.12';
$fileDirHandler_mimeT['xltx'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.template';
$fileDirHandler_mimeT['xml'] = 'application/xml';
$fileDirHandler_mimeT['xo'] = 'application/vnd.olpc-sugar';
$fileDirHandler_mimeT['xop'] = 'application/xop+xml';
$fileDirHandler_mimeT['xpi'] = 'application/x-xpinstall';
$fileDirHandler_mimeT['xpm'] = 'image/x-xpixmap';
$fileDirHandler_mimeT['xpr'] = 'application/vnd.is-xpr';
$fileDirHandler_mimeT['xps'] = 'application/vnd.ms-xpsdocument';
$fileDirHandler_mimeT['xpw'] = 'application/vnd.intercon.formnet';
$fileDirHandler_mimeT['xslt'] = 'application/xslt+xml';
$fileDirHandler_mimeT['xsm'] = 'application/vnd.syncml+xml';
$fileDirHandler_mimeT['xspf'] = 'application/xspf+xml';
$fileDirHandler_mimeT['xul'] = 'application/vnd.mozilla.xul+xml';
$fileDirHandler_mimeT['xwd'] = 'image/x-xwindowdump';
$fileDirHandler_mimeT['xyz'] = 'chemical/x-xyz';
$fileDirHandler_mimeT['yaml'] = 'text/yaml';
$fileDirHandler_mimeT['yang'] = 'application/yang';
$fileDirHandler_mimeT['yin'] = 'application/yin+xml';
$fileDirHandler_mimeT['zaz'] = 'application/vnd.zzazz.deck+xml';
$fileDirHandler_mimeT['zip'] = 'application/zip';
$fileDirHandler_mimeT['zir'] = 'application/vnd.zul';
$fileDirHandler_mimeT['zmm'] = 'application/vnd.handheld-entertainment+xml';


/*
Examples

set working Path

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/algo.md");

//or

$fdh = new fileDirHandler();
$fdh->SetPath("Red/Hot/Chilli/Peppers/readme.txt");



Write to a file

fileDirHandler
If want to write text to a file.

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/readme.txt");
$fdh->Write('hello world');


Read text from a file
you can get all text content from a file.

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/readme.txt");
$content = $fdh->Read(); 
echo nl2br($content);


Create directory or file

You can create directories and files. With this method you also creates the parent directories if they doesn't exist.

2 parameters:

1- create directory (default false)
2- overwrite, overwrite file or if exist (default false)


//Create directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/new");
$fdh->Create(true);

//Create file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create();


//Create file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create();

//overwrite file exist
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create(false,true);


Delete directory or file

be careful about permissions, if not the file or folder can't be deleted.

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Delete();


Copy
Copy a directory or file. 
has one parameter and that's the directory and filename to where you want to copy the file.
in the case of copy an entire directory the parameter is the destination directory.

the destination directory must exist

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Copy("Red/Hot/Chilli/Peppers/new/Californication2.txt");

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Parallel universe/");
$fdh->Copy("Red/Hot/Chilli/Peppers/new/");

Move

similar to copy, but in this case the files are moved to the new destination
and can also use this function as directory an file renamer.


$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->move("Red/Hot/Chilli/Peppers/new/x.txt");


Listing

If directory is selected the method returns a list of files and directories that are inside the directory. 

4 optitional parameters:

Excluded extensions: Array of extension that you want to exclude from the result. For example array("jpg")
Excluded files: Array of files that you want to exclude from the result. For example array("Thumb.db")
Exclude directories: Array of directories you want to exclude from the result. For example array("backup", "temp")
Recursive: Set to true if you also want to list the content of subdirectories

the array returned contains an associative array with the file information:

[0] => Array
        (
            [type] => file
            [parentDir] => music/
            [fileName] => Under the bridge.mp3
            [name] => Under the bridge
            [extension] => mp3
            [mimeType] => audio/mpeg
            [fullpath] => music/Under the bridge.mp3
        )
		
in the case of directory:

[0] => Array
        (
            [type] => dir
            [parentDir] => music/
            [name] => folder
            [fullpath] => music/folder
        )

$fdh = new fileDirHandler("music/");
$array = $fdh->Listing(); 
print "<pre>";
print_r($array);
print "</pre>";


getPath
this function return the selected path in the object

$fdh = new fileDirHandler("music/Universally speaking.mp3");
echo $fdh->getPath(); 


once you have set the path can access to some properties:

- parentDir: Parent directory's path
- name: name of the file or directory without extension
- fileName: name of the file or directory, if is file contain extension
- extension: file extension if has	
- mimeType: Multipurpose Internet Mail Extensions,containing a type and a subtype in a string e.g. .jpg image is "image/jpeg"
- type: type of path directory or file (dir/file)
- exist: if the file or directory exist 


$fdh = new fileDirHandler("music/13 - Universally  speaking.mp3");
echo $fdh->getPath()."<br>";
echo $fdh->parentDir."<br>";
echo $fdh->name."<br>";
echo $fdh->fileName."<br>";
echo $fdh->extension."<br>";
echo $fdh->mimeType."<br>";
echo $fdh->type."<br>";
echo $fdh->exist."<br>";

mimeType fileDirHandler::mimeType();
you can get the MIME type (Multipurpose Internet Mail Extensions) of any file 

if mime type is not found in a predefined list(691 common types), it can try to use PHP mime_content_type function, but file must exist.

echo fileDirHandler::getMimeType("By the way.mp3");

//returns "audio/mpeg"


*/



/*$zip = new ZipArchive;
if ($zip->open('test_new.zip', ZipArchive::CREATE) === TRUE)
{
    // Add files to the zip file
    $zip->addFile('test.txt');
    $zip->addFile('image.png');
 
    // Add random.txt file to zip and rename it to newfile.txt
    $zip->addFile('image.png', 'Newimage.png');
 
    // Add a file new.txt file to zip using the text specified
    $zip->addFromString('new.txt', 'text to be added to the new.txt file');
 
    // All files are added, so close the zip file.
    $zip->close();
}*/

/*
$fdh = new fileDirHandler("recursive/algo/");
$fdh->zipCreate("file.zip",true,true);

*/
//$fdh = new fileDirHandler("recursive/algo/");
//$fdh = new fileDirHandler("image.png");

$filesStr = "test.txt,image.png,class.logApp.php,class.fileDirHandler.php,recursive/algo,noFile.txt";

$files = array();
$files[] = "test.txt";
$files[] = "image.png";
$files[] = "class.logApp.php";
$files[] = "class.fileDirHandler.php";
$files[] = "recursive/algo";
$files[] = "noFile.txt";



//$fdh->makeFileList($filesStr);
//$fdh->makeFileList($files,true);
//$fdh->makeFileList($fdh->Listing(true),false,false,"rafa",$fdh->getPath());
	
//$fdh->zipCreate("prueba.zip",true,true,true);

//$fdh->zipCreate("pruebax.zip",true,true,true,"rafa");
	
//$fdh->zipCreate("pruebax.zip",true,true,true,"rafa",$filesStr);
//$fdh->zipCreate("pruebax.zip",true,true,true,"rafa",$files);
//$fdh->zipCreate("pruebax.zip",true,true,true,true,"rafa",$fdh->Listing(false));
/*$fdh = new fileDirHandler("pruebax.zip");
$fdh->zipListing();*/


foreach (glob("t*t") as $nombre_fichero) {
    echo "Tamaño de $nombre_fichero " . filesize($nombre_fichero) . "<br>";
}


var_dump(glob("*"));

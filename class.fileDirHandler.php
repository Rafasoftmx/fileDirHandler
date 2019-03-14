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
                                                                  
                                                             
                                                               
                                                                            

* Simple class to handle some useful functions for directory or files and zip compression
* get info of files, like mimetype, copy, move, create files an directories, 
* add complete folders to a Zip file, extract, copy, rename, create even in a two differents  zip files
* see examples at the end of this file

*/


class fileDirHandler
{
	
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
	public $mimeType = ""; // MIME type -Multipurpose Internet Mail Extensions-, containing a type and a subtype in a string e.g. .jpg image is "image/jpeg"
	public $type = ""; //type of path directory or file ("dir" or "file")
	public $exist = false; // if the file or directory exist in the file system
	
	public $tempDir = "fileDirHandler_temp"; // temp directory for some zip functions of the class, is created and deleted when is used 

	
	/**
	 * Constructor
	 * 
	 * @access public
	 * @return void
	 * @param string $path
	 */
	public function __construct($path = "")
	{
		$this->SetPath($path);		
	}	
	
	/**
	 * Determines if a directory is empty
	 *
	 * @access public
	 * @return bool
	 * @param string dir
	 */
	private function is_dirEmpty($dir)
	{		
		$handle = opendir($dir);
		  while ( false !== ($entry = readdir($handle)) ) 
		  {
			if ($entry != "." && $entry != "..") {
			  closedir($handle);
			  return FALSE;
			}
		  }
		  closedir($handle);
		  return TRUE;
	}
	
	/**
	 * function to determine if an array contain a list of files created for the class
	 * 
	 * @access public
	 * @return void
	 * @param array $arr
	 */
	private function is_arrayListFiles($arr)
	{
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
						elseif(array_key_exists("name", $arr[0]))// it means is an array from this class
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
	private function lengthSort($val_1, $val_2)
	{

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
	 * clean a path, replace backslash and clean the last slash if exist
	 *
	 * @access private
	 * @param string $path
	 * @return string
	 */
	private function fixPath($path="")
	{
		$path = str_replace("\\", "/", $path);
		$path = (substr($path, -1) == "/") ? substr($path, 0, -1) : $path;

		return $path;
	}
	
	
	

	/**
	 * get the current path
	 *
	 * @access public
	 * @return string
	 */
	public function getPath()
	{
		return $this->path;
	}


	/**
	 * Sets the pointing route in the file system and sets some properties in the class
	 *
	 * @access public
	 * @param string $path
	 * @return void
	 */
	public function SetPath($path="")
	{		
		if($path != "")
		{ 			
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
			$this->type ="file"; // efine before just in case file no exist yet, it's supposition
		}
		else
		{
			$this->type ="dir"; //define before just in case file no exist yet, it's supposition
			
		}	
		
		
		
		if(is_file ($this->path))// if file exist
		{
			$this->type ="file";
			$this->exist = true;
		}
		else if(is_dir($this->path))// if file exist
		{
			$this->type ="dir";
			$this->exist = true;
		}
		
	}

	
	/**
	 * Get MIME Type of a file -Multipurpose Internet Mail Extensions-, containing a type and a subtype in a string e.g. .xls is "application/vnd.ms-excel"
	 *
	 * @access public static
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
	 * Read file content using fopen and fread, return all file contents
	 * 
	 * @access public
	 * @return string
	 */
	public function Read()
	{
		$handle = fopen($this->path, "r");
		$contents = fread($handle, filesize($this->path));
		fclose($handle);
		return $contents;
	}
	
	/**
	 * Write string content to file using fopen and fwrite
	 * 
	 * @access public
	 * @param string contents
	 * @param bool overwrite
	 * @return bool
	 */
	public function Write($contents,$overwrite=false)
	{
		
		$flag = $overwrite ? "w" : "a";
		
		if($this->exist == false)
		{
			$this->Create();
		}
		
		if($handle = fopen($this->path,$flag))
		{	
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
	public function Create($overwrite = false)
	{
		if(file_exists($this->path) && !$overwrite ) return false;
		
		if($this->type == "file")
		{
			$parts = explode("/", $this->path);
			$path = "";
			foreach ($parts as $part)
			{
				if($part == end($parts)) break;
				$path .= $part . "/";
				@mkdir($path, $this->permissions);				
			}
			if($handle = fopen($this->path, 'w'))
			{
				fclose($handle);
			}
		}
		else
		{
			$parts = explode("/", $this->path);
			$path = "";
			foreach ($parts as $part)
			{
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
	 * @param bool onlyFilesInsideDir 
	 */
	public function Delete($onlyFilesInsideDir = false)
	{
		if(is_dir($this->path) && $this->path != "")
		{
			$result = $this->listDir();
			
			// makes a map and sort them to progressive deletion, from files to directories
			$sort_result = array();
			
			foreach($result as $item)
			{
				if($item['type'] == "file")
				{
					array_unshift($sort_result, $item);
				}
				else
				{
					$sort_result[] = $item;
				}
			}
			
			$trys =0;
			$continue = true;
			// Start deleting
			while($continue)
			{
				if(is_array($sort_result))
				{
					foreach($sort_result as $item)
					{
						if($item['type'] == "file")
						{
							@unlink($item['fullpath']);
						}
						else
						{
							@rmdir($item['fullpath']);
						}
					}
				}
				
				
				$trys++;
				if ($trys >= 200){break;} //max trys to delete directory
				
				
				if($onlyFilesInsideDir == true )// only removes de files and dirs inside
				{
					$continue = !$this->is_dirEmpty($this->path); // while dir contains files
				}				
				else
				{
					@rmdir($this->path);// remove the directory path
					$continue = file_exists($this->path); // wile dir exist
				}
				
			}
			return !$continue;
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
	 * @param string $includeSelfFolder
	 * @return bool
	 */
	public function Copy($destination, $includeSelfFolder = false)
	{
		if($destination == "") {throw new Exception("Destination is not specified.");}
		if($this->exist == false){throw new Exception("file that try to copy not exist");}
			
		$destination = $this->fixPath($destination);
	
		if($this->type == "dir")
		{
			
			if($includeSelfFolder)
			{
				$destination .= "/".$this->fileName;//adds the folder name of the directory that is copied
			}			
			
			// Create paths recursively
			$result = $this->listDir();
			$paths = array();
			$files = array();
			foreach ($result as $item)
			{
				if($item["type"] == "dir")
				{
					$pathToReplace = '/'.preg_quote($this->path, '/').'/';
					$paths[] = preg_replace($pathToReplace,"",$item["fullpath"],1); // replace the first coincidence
				}
				else
				{
					$pathToReplace = '/'.preg_quote($this->path, '/').'/';
					$file = preg_replace($pathToReplace,"",$item["fullpath"],1);	// replace the first coincidence				
					$files[] = (substr($file, 0, 1) == "/") ? $file : "/" . $file;
				}
			}
			
			//Sort paths based on the strings lengths
			uasort($paths, array($this, "lengthSort"));
			
			
			// Create directory structure
			foreach ($paths as $path)
			{
				$path = (substr($path, 0, 1) == "/") ? $path : "/" . $path;
				$new_directory = $destination . $path;
				
				if(!file_exists($new_directory))
				{
					$fdh = new fileDirHandler($new_directory);
					$fdh->Create();
				}
			}
			
			
			$fdh = new fileDirHandler($destination);
			if($fdh->type == "file")
			{
				throw new Exception("destination can't be a file when try to copy a directory");
			}
			
			// Copy files
			foreach ($files as $file)
			{

			 
				if($fdh->exist == false)// if destination directory not exist
				{
					$fdh->Create();
				}	
				@copy($this->path . $file, $destination . $file);
			}
			
			return file_exists($destination);
		}
		else
		{	
			$fdh = new fileDirHandler($destination);
			
			if($fdh->type == "dir")// if is directory
			{
				if($fdh->exist == false)// if directory destination not exist
				{
					$fdh->Create();					
				}				
				$destination .= "/".$this->fileName; // includes the filename of the file that we are coping
			}
		 	else
			{	
				$fdh->SetPath($fdh->parentDir);// the directory destination of the file
				
				if($fdh->exist == false)// if directory destination not exist
				{
					$fdh->Create();					
				}
			}
			
			

			
			@copy($this->path, $destination);
			
			return file_exists($destination);
		}

	}
	
	/**
	 * Move directory or file
	 * 
	 * @access public
	 * @param string $destination
	 * @param string $includeSelfFolder
	 * @access void
	 */
	public function Move($destination,$includeSelfFolder = false)
	{
		
		$copyResult = $this->Copy($destination,$includeSelfFolder);
		
		if($copyResult)
		{
			if($this->type == "dir" && $includeSelfFolder == false)// if only move the contents of directory just delete the files that we moved
			{
				$this->Delete(true);
			}
			else// else we delete all the directory or file we move
			{
				$this->Delete();
			}
			
		}		
		return ($copyResult && !file_exists($this->path));
	}
	
	/**
	 * List directory content
	 * 
	 * @access public
	 * @param bool $recursive
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @param array $list
	 * @param string $dir
	 * @return array
	 */
	public function listDir($recursive=true, $exclude_extension=array(), $exclude_file=array(), $exclude_dir=array(), &$list=array(), $dir="")
	{

		// Lowercase excluded arrays
		$exclude_extension = array_map("strtolower", $exclude_extension);
		$exclude_file = array_map("strtolower", $exclude_file);
		$exclude_dir = array_map("strtolower", $exclude_dir);
		
		$dir = ($dir == "") ? $this->path : $dir;
		if(substr($dir, -1) != "/") $dir .= "/";

		// Open folder 
		$dir_handle = @opendir($dir) or die("Unable to open $dir"); 

		// Loop files 
		while ($file = readdir($dir_handle))
		{			
			$extension="";	
			$ruteParts = pathinfo($dir . $file);

			if (array_key_exists("extension",$ruteParts))
			{
				$extension = strtolower($ruteParts['extension']);
			}			
			
			// omit dots and extension excluded
			if($file == "." || $file == ".." || in_array($extension, $exclude_extension)){ continue; }
			
			if(is_dir($dir . $file))
			{
				if(!in_array(strtolower($file), $exclude_dir))
				{
					$info				= array();
					$info["type"]		= "dir";
					$info["parentDir"]	= $dir;
					$info["name"]		= $file; 
					$info["fullpath"]	= $dir . $file;
					$list[] = $info;
				}
			}else{
				if(!in_array(strtolower($file), $exclude_file))
				{
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
			
			if($recursive && is_dir($dir . $file) && !in_array(strtolower($file), $exclude_dir))
			{
				$this->listDir($recursive, $exclude_extension, $exclude_file, $exclude_dir, $list, $dir . $file);
			}
			
		} 
		
		// Close 
		closedir($dir_handle); 
		
		return $list;
		
	}
	
	/**
	 * List directory content in a simple numeric array with the files and directories
	 * 
	 * @access public
	 * @param bool $recursive
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @param array $list
	 * @param string $dir
	 * @return array
	 */
	public function simpleListDir($recursive=true, $exclude_extension=array(), $exclude_file=array(), $exclude_dir=array(), &$list=array(), $dir="")
	{

		// Lowercase excluded arrays
		$exclude_extension = array_map("strtolower", $exclude_extension);
		$exclude_file = array_map("strtolower", $exclude_file);
		$exclude_dir = array_map("strtolower", $exclude_dir);
		
		$dir = ($dir == "") ? $this->path : $dir;
		if(substr($dir, -1) != "/") $dir .= "/";

		// Open folder 
		$dir_handle = @opendir($dir) or die("Unable to open $dir"); 

		// Loop files 
		while ($file = readdir($dir_handle)) 
		{ 			
			$extension="";	
			$ruteParts = pathinfo($dir . $file);

			if (array_key_exists("extension",$ruteParts))
			{
				$extension = strtolower($ruteParts['extension']);
			}			
			
			// omit dots and extension excluded
			if($file == "." || $file == ".." || in_array($extension, $exclude_extension)){ continue; }

			if(is_dir($dir . $file))
			{
				if(!in_array(strtolower($file), $exclude_dir))
				{
					$list[] = $dir . $file;
				}
			}else{
				if(!in_array(strtolower($file), $exclude_file))
				{
					$list[] = $dir . $file;
				}
			}

			if($recursive && is_dir($dir . $file) && !in_array(strtolower($file), $exclude_dir))
			{
				$this->simpleListDir($recursive, $exclude_extension, $exclude_file, $exclude_dir, $list, $dir . $file);
			}
			
		} 
		
		// Close 
		closedir($dir_handle); 
		
		return $list;
		
	}
	
	/**
	 * create a simple numeric array from the file list created from this class
	 * 
	 * @access private
	 * @param array $files
	 * @return array
	 */
	private function getSimpleList($files=null)
	{
		if($files == null){return;}
		
		$listeFiles = array();
		foreach($files as $file)
		{
			if(array_key_exists("fullpath", $file))// it means is an array from this class
			{
				$listeFiles[] = $file["fullpath"];
			}
			elseif(array_key_exists("name", $file))// it means is an array from this class
			{
				$listeFiles[] = $file["name"];
			}
			
		}	
		
		return $listeFiles;
	}
	
	
	
	/**
	 * normalize list of files to simple numeric array, acept file list created from this class and comma separated string
	 * 
	 * @access public
	 * @param mixed $files
	 * @return void
	 */
	private function normalizeFilesList($files=null)
	{
		// normalize $files to simple numeric array
		if(is_array($files))
		{
			if($this->is_arrayListFiles($files))// if is associative array from this class
			{
				$files = $this->getSimpleList($files);
			}
		}
		elseif(is_string ($files))
		{
			$filesStr = explode(",", $files);
			$files = array();
			foreach($filesStr as $f)
			{
				if(is_numeric($f))
				{
					$files[] = intval($f);
				}
				else
				{
					$files[] = trim($f);
				}				 
			}
			
		}	
		
		return $files;
	}
	
	/**
	 * return array with file information to add zip file. fullpath= file path, type = "dir" or "file", zipFilePath= Path in zip file
	 * 
	 * @access public
	 * @param string $fullpath
	 * @param string $type
	 * @param string $rootPath
	 * @param string $includeSelfFolder
	 * @param string $createOnPath
	 * @return array
	 */
	private function getZipFileInfo($fullpath="",$type="",$rootPath="",$includeSelfFolder = false,$createOnPath = "")
	{				
		$file= array();
		$file["fullpath"] =$fullpath;
		$file["type"] =$type;
		
		$rutePartsFile = pathinfo($file["fullpath"]);
		$fileName = $rutePartsFile["basename"];
		$SelfFolder = "";		

		
		if($createOnPath != "")
		{
			$createOnPath = $this->fixPath($createOnPath)."/";
		}
		
		if($includeSelfFolder == true && $rootPath != "")
		{
			$partsRootPath = pathinfo($rootPath);
			$SelfFolder = $partsRootPath["basename"]."/";
		}
		
		
		if($rootPath != "")
		{
			$pathToReplace = '/'.preg_quote($rootPath, '/').'/';// erase "rootPath" from "file Path"
			$newFilePath = preg_replace($pathToReplace,"",$file["fullpath"],1);			
			$newFilePath = (substr($newFilePath, 0,1) == "/") ? substr($newFilePath, 1,strlen($newFilePath)) : $newFilePath;	// delete "/" in the begin if exist
			
			$file["zipFilePath"] = $createOnPath.$SelfFolder.$newFilePath;
		}
		else
		{
			$file["zipFilePath"] = $createOnPath.$SelfFolder.$fileName;
		}
		
		return $file;
	}

	
	/**
	 * makes and return a list of files with the information to add in the zip file
	 * 
	 * @param mixed $files
	 * @param bool $recursive
	 * @param bool $includeSelfFolder
	 * @param string $createOnPath
	 * @param string $rootPath
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @access public
	 * @return array
	 */
	private function getZipFileList($files = null,$recursive = true,$includeSelfFolder = false,$createOnPath = "",$rootPath="",$exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{	
		if($files == null){ return;}
		
		$listFiles =  array();
		
		$files = $this->normalizeFilesList($files);
		
		foreach($files as $item)
		{
			$item = $this->fixPath($item);

			if(is_file ($item))// is file
			{
				$listFiles[] = $this->getZipFileInfo($item,"file",$rootPath,$includeSelfFolder,$createOnPath);
			}
			elseif(is_dir($item))// is dir
			{
				//include the the folder
				$rootPathDir = $this->getZipFileInfo($item,"dir",$rootPath,$includeSelfFolder,$createOnPath);
				if($rootPathDir["fullpath"] != "")
				{
					$listFiles[] = $rootPathDir;
				}
				
				//include the files inside the folder, if is recursive includes sub folders
				$fdh = new fileDirHandler($item);
				$dirFiles = $fdh->listDir($recursive, $exclude_extension, $exclude_file, $exclude_dir);

				foreach($dirFiles as $dirFile)
				{
					$listFiles[] = $this->getZipFileInfo($dirFile["fullpath"],$dirFile["type"],$rootPath,$includeSelfFolder,$createOnPath);
				}
			}
			else
			{
				$ruteParts = pathinfo($item);

				if (array_key_exists("extension",$ruteParts))
				{
					$listFiles[] = $this->getZipFileInfo($item,"file",$rootPath,$includeSelfFolder,$createOnPath);
				}
				else
				{
					$listFiles[] = $this->getZipFileInfo($item,"dir",$rootPath,$includeSelfFolder,$createOnPath);
				}
			}
		}
		
		return $listFiles;
	}
	

	
	/**
	 * create zip file with the file or directory path set, or define list of files or directories to add
	 * 
	 * @param string $zipName
	 * @param bool $overwrite
	 * @param bool $recursive
	 * @param bool $includeSelfFolder
	 * @param bool $includeEmptyFolders
	 * @param string $createOnPath
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @access public
	 * @return void
	 */
	public function zipCreate($zipName = "",$overwrite = false,$recursive = true,$includeSelfFolder = false,$includeEmptyFolders= true, $createOnPath = "",$fromFiles= null, $exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{
		
		
		$flags = $overwrite ?  ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE;
		
		if(is_file($zipName) == false && $overwrite == true) // if file not exist and overwrite is true
		{
			$flags = ZIPARCHIVE::CREATE;
		}
		
		
		
		
		$files= array();
		$dirs= array();
		
		if($fromFiles!== null)
		{			
			$fromFiles = $this->normalizeFilesList($fromFiles);
			
			foreach($fromFiles as $item)
			{
				$item = $this->fixPath($item);
				
				if(is_dir($item))
				{
					$files = array_merge($files,$this->getZipFileList($item,$recursive,$includeSelfFolder,$createOnPath,$item,$exclude_extension,$exclude_file,$exclude_dir));		
				}
				else
				{
					$files = array_merge($files,$this->getZipFileList($item,false,false,$createOnPath,"",$exclude_extension,$exclude_file,$exclude_dir));
				}				
			}
			
		}		
		elseif($this->type == "dir")
		{			
			$files = $this->getZipFileList($this->path,$recursive,$includeSelfFolder,$createOnPath,$this->path,$exclude_extension,$exclude_file,$exclude_dir);		
		}
		elseif($this->type == "file")
		{
			$files = $this->getZipFileList($this->path,false,false,$createOnPath,"",$exclude_extension,$exclude_file,$exclude_dir);
		}		
		
		if(count($files) > 0)
		{
			$zip = new ZipArchive;
			if ($zip->open($zipName, $flags) === true)
			{
				foreach($files as $file)
				{					
					if($file["type"] == "file")
					{
						$zip->addFile($file["fullpath"],$file["zipFilePath"]);
					}
					else
					{						
						if($includeEmptyFolders)
						{							
							$zip->addEmptyDir ($file["zipFilePath"]);	
						}						
					}
				}
				$zip->close();
			}
			
		}
		
	}
	
	
	
	
	/**
	 * lists the files and directories inside a zip file,returns associative array with information of each file/directory
	 *
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @access public
	 * @return array
	 */
	public function zipListing($exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{	
		// Lowercase excluded arrays
		$exclude_extension = array_map("strtolower", $exclude_extension);
		$exclude_file = array_map("strtolower", $exclude_file);
		$exclude_dir = array_map("strtolower", $exclude_dir);
		
		$list = array();
		
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{
			for( $i = 0; $i < $zip->numFiles; $i++ )
			{ 
				$file = $zip->statIndex( $i ); 
				$filePath = $file['name'];
					
				$extension="";	
				$ruteParts = pathinfo($filePath);

				if (array_key_exists("extension",$ruteParts))
				{
					$extension = strtolower($ruteParts['extension']);
				}	
			
			
				// omit extension excluded
				if(in_array($extension, $exclude_extension)){ continue;}
				
				
				//
				if($extension == "" && substr($filePath, -1) == "/"){
					if(!in_array(strtolower($ruteParts["basename"]), $exclude_dir))
					{
						$file["type"] = "dir";	
						$file["comment"] = $zip->getCommentIndex($i);
						$list[] = $file;
					}
				}else{
					if(!in_array(strtolower($ruteParts["basename"]), $exclude_file))
					{
						$file["type"] = "file";
						$file["comment"] = $zip->getCommentIndex($i);
						$list[] = $file;
					}
				}				
				
				

			}
			$zip->close();
		} 
		
		return $list;
		
	}
	
	/**
	 * lists the files and directories inside a zip file, returns numeric array with the names of each file/directory
	 *
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @access public
	 * @return array
	 */
	public function zipSimpleListing($exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{	
		// Lowercase excluded arrays
		$exclude_extension = array_map("strtolower", $exclude_extension);
		$exclude_file = array_map("strtolower", $exclude_file);
		$exclude_dir = array_map("strtolower", $exclude_dir);
		
		$list = array();
		
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{
			for( $i = 0; $i < $zip->numFiles; $i++ )
			{ 
				$file = $zip->statIndex( $i ); 		
				$list[] = $file['name'];
			}
			$zip->close();
		} 
		
		return $list;
		
	}
	
	
	
	/**
	 * return an array with the information of the file inside the zip, false if no found
	 *
	 * @param mixed $file
	 * @access public
	 * @return mixed
	 */
	public function getZiFileInfo($file)
	{	

		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{
			$info = array();
			
			if(is_string($file))
			{				
				$info = $zip->statName($file);
			}
			elseif(is_numeric($file))
			{
				$info = $zip->statIndex($file);
			}			
			
			
			if($info == false)
			{
				//in the case of directories, sometimes not found in the zip file, but exist
				if(substr($file, -1) == "/") //if is dir try to get the information
				{
					$info = array();
					$info["name"] = $file;
					$info["type"] = "dir";
				}
				
				return $info;
			}
				
			if(substr($info["name"], -1) == "/") //if is dir
			{
				$info["type"] = "dir";	
			}
			else
			{
				$info["type"] = "file";
			}

			$info["comment"] = $zip->getCommentIndex($info["index"]);

			
			return $info;
			
			$zip->close();
		} 
		
		
		
	}
	
	
	
	
	/**
	 * delete a list of files inside the zip, if is directory it deletes recursively
	 *
	 * @param mixed $file
	 * @access public
	 * @return void
	 */
	public function zipDelete($files = array())
	{	
		
		$files = $this->normalizeFilesList($files);
		
		
		// every time is deleted a files the indexes in the zip change
		// by this reason first get the info of every file and after delete by name, to avoid delete by index in a moved indexes
		foreach($files as &$file)
		{
			$file = $this->getZiFileInfo($file);// get the info of files by name or index
		}
		
		$zip = new ZipArchive;
		
		if ($zip->open($this->path) === TRUE) 
		{			
			foreach($files as &$file)
			{								
				if($file == false){continue;}
				
				$zip->deleteName($file["name"]);

				if($file["type"] == "dir")
				{
					$zipFiles = $this->zipListing();
					foreach($zipFiles as $f)
					{
						if(substr($f["name"], 0, strlen($file["name"])) == $file["name"]) // if begins whit the dir name
						{
							$zip->deleteName($f["name"]);
						}
					}					
				}

			}
			
			$zip->close();			
		}
	}
	
	/**
	 * Add files to an existing zip with the file or directory path set, or a list of files or directories to add
	 *
	 * 
	 * @param string $zipName	 
	 * @param bool $recursive
	 * @param bool $includeSelfFolder
	 * @param bool $includeEmptyFolders
	 * @param string $addOnPath
	 * @param mixed $fromFiles
	 * @param array $exclude_extension
	 * @param array $exclude_file
	 * @param array $exclude_dir
	 * @access public
	 * @return void
	 */
	public function zipAdd($zipName = "",$recursive = true,$includeSelfFolder = false,$includeEmptyFolders= true, $addOnPath = "",$fromFiles= null, $exclude_extension=array(), $exclude_file=array(), $exclude_dir=array())
	{
		// just change the $overwrite param to false for adding files
		$this->zipCreate($zipName,false,$recursive,$includeSelfFolder,$includeEmptyFolders,$addOnPath,$fromFiles,$exclude_extension,$exclude_file,$exclude_dir);
		
	}
	
	/**
	 * add comment to the entire zip file or each individual file
	 *
	 * @param string $archiveComment
	 * @param mixed $files
	 * @access public
	 * @return string
	 */
	public function setZipComment($archiveComment = "",$files = array())
	{
		
		
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{
			if($archiveComment != "")
			{
				$zip->setArchiveComment($archiveComment);
			}
			
			
			
			foreach($files as $file=>$comment)
			{	
				$file = $this->getZiFileInfo($file);
				if(!$file){return;}
				
				if(is_string($comment))
				{
					$zip->setCommentIndex($file["index"],$comment);
				}
			}
			$zip->close();
		}
	}
	
	/**
	 * return the comment to the entire zip
	 *
	 * @access public
	 * @return string
	 */
	public function getZipComment()
	{		
		$comment ="";
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{
			$comment = $zip->getArchiveComment();
			$zip->close();
		}
		return $comment;
	}
	
	
	/**
	 * Read and return the content of file inside a zip
	 * 
	 * @access public
	 * @param mixed file
	 * @return string
	 */
	public function zipRead($file = "")
	{
		if($file == ""){return;}		
		
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{			
			$file = $this->getZiFileInfo($file);
			if($file!= false)
			{
				return $zip->getFromName($file["name"]);
			}			
			
			$zip->close();
		}
		return "";
	}
	
	/**
	 * Write string content to file inside a zip, creates the file if not exist
	 * 
	 * @access public
	 * @param mixed file
	 * @param string $contents
	 * @param bool $overwrite
	 * @return string
	 */
	public function zipWrite($file = "",$content ="",$overwrite=false)
	{
		
		if($file == "" || $content == ""){return;}
		
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE)
		{
			
			$fileInfo = $this->getZiFileInfo($file);
			if($fileInfo != false)
			{				
				if($overwrite == false)
				{
					$content = $this->zipRead($fileInfo["name"]) . $content;
				}

				$zip->addFromString($fileInfo["name"], $content);
			}
			else
			{
				$zip->addFromString($file, $content);
			}
			

			
			$zip->close();
		}
		return $content;
	}
	
	
	/**
	 * extract the content of the entire zip file, or a list of files / directories
	 *
	 * @param string $destination
	 * @param mixed $files
	 * @access public
	 * @return bool
	 */
	public function zipExtract($destination  = "",$files = null)
	{			
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE)
		{	
			if($files != null)
			{				
				$files = $this->normalizeFilesList($files);
				
				$filesToExtract = array();
				$zipFiles = $this->zipListing();
				
	
					foreach($files as $file)
					{
						
						$file = $this->getZiFileInfo($file);
						if($file == false){continue;}
						
						if($file["type"] == "dir")
						{
							foreach($zipFiles as $f)
							{	
								if(substr($f["name"], 0, strlen($file["name"])) == $file["name"]) // if begins whit the dir name
								{
									$filesToExtract[] = $f["name"];
								}
							}							
						}
						else
						{
							$filesToExtract[] = $file["name"];
						}
					}

				$filesToExtract = array_unique($filesToExtract);
				
				$zip->extractTo($destination,$filesToExtract);
				
			}
			else
			{
				$zip->extractTo($destination);
			}
			
			$zip->close();
		}
		return;
		
	}
	
	/**
	 * copy a list of files to other path in the same zip and if is defined can copy to another zip file also
	 *
	 * @access public
	 * @return bool
	 */
	public function zipCopy($files = null,$destination = "",$otherZipFile = "")
	{		
		$files = $this->normalizeFilesList($files);
		
		$this->zipExtract($this->tempDir,$files);
		
		$fdh = new fileDirHandler($this->tempDir);
		if($otherZipFile == "")
		{
			$fdh->zipAdd($this->path,true,false,true,$destination);		
		}
		else
		{
			if(is_file($otherZipFile))
			{
				$fdh->zipAdd($otherZipFile,true,false,true,$destination);
			}
		}		
		
		$fdh->SetPath($this->tempDir);
		$fdh->Delete();		
	}
	
	
	/**
	 * Move a list of files to other path in the same zip and if is defined can move to another zip file also
	 *
	 * @param mixed $files
	 * @param string $destination
	 * @param string $otherZipFile
	 * @access public
	 * @return bool
	 */
	public function zipMove($files = null,$destination = "",$otherZipFile = "")
	{	
		$files = $this->normalizeFilesList($files);
		
		if($destination != "")
		{
			$destination = $this->fixPath($destination)."/";
		}
		
		// move to other zip file and delete the files in this one
		if($otherZipFile != "")
		{
			if(is_file($otherZipFile))
			{
				$this->zipCopy($files, $destination,$otherZipFile);
				$this->zipDelete($files);
			}
			return;
		}
		
		
		//rename the files to move in the same zipfile
		$zip = new ZipArchive;
		if ($zip->open($this->path) === TRUE) 
		{			
			
			foreach($files as $file)
			{	
				
				$file = $this->getZiFileInfo($file);
				if(!$file){continue;}
				
				if($file["type"] == "dir")
				{
					$ruteParts = pathinfo($file["name"]);
					$newPath = $destination.$ruteParts['basename']."/";

					$zip->renameName($file["name"],$newPath);

					$zipFiles = $this->zipListing();
					foreach($zipFiles as $f)
					{
						if(substr($f["name"], 0, strlen($file["name"])) == $file["name"]) // if begins whit the dir name
						{

							$pathToReplace = '/'.preg_quote($file["name"], '/').'/';// change "old dir name" from "file Path" to a "new file Path"
							$newFilePath = preg_replace($pathToReplace,$newPath ,$f["name"],1);

							$zip->renameName($f["name"],$newFilePath);
						}
					}
					
				}
				else
				{
					$ruteParts = pathinfo($file["name"]);
					$newPath = $destination.$ruteParts['basename'];

					$zip->renameName($file["name"],$newPath);						
				}
				
			}
			$zip->close();
		}
		
	}

	
}//EOC




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


If want to create and write text to a file.
if the path doesn't exist it will be created

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/readme.txt");
$fdh->Write('hello world');// create if not exist

$fdh->Write('overwrite hello world',true);//overwrite previous content

$fdh->Write(', hello again');//add this content

parameters
1. string contents: text to write in file
2. bool overwrite:  defines if content is for add or rewrite the file (default false)



Read text from a file
you can get all content from a file.

$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/readme.txt");
$content = $fdh->Read(); 
echo nl2br($content);


Create directory or file
You can create directories and files, with this method you also creates the parent directories if they doesn't exist.

parameters
1- bool overwrite: overwrite file if exist (default false)


//Create directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->Create();

//Create file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create();


//overwrite file if exist
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create(true);


Delete directory or file
be careful about permissions, if not the file or folder can't be deleted.

parameters
1. bool onlyFilesInsideDir: if true delete only the files and directories not the folder itself  (default false)

// delete a file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Delete();

// delete folder "Peppers" and its content
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->Delete();

// only delete the content of "Peppers" keeping the folder "Peppers"
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->Delete(true);



Copy

copies the entire directory or file to other destination
if destination directory not exist is created


parameters
1. string destination: the path where we going to copy the files
2. bool includeSelfFolder: if true creates the folder and content in the destination, otherwise only the files inside the folder will be copied (default false)

copy file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Copy("Red/Hot/Chilli/Peppers/new/Californication2.txt"); //also renames the file

copy directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Parallel universe/");
$fdh->Copy("Red/Hot/Chilli/Peppers/new/");

copy file in directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Copy("Red/Hot/"); 



Move

Similar to copy, but in this case the files are moved to the new destination
and can also use this function as directory an file renamer.
if destination directory not exist is created.

parameters
1. string destination: the path where we going to move the files
2. bool includeSelfFolder: if true creates the folder and content in the destination, otherwise only the files inside the folder will be copied (default false)


move file in a new directory with same name
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->move("Red/Hot/Chilli/Peppers/new/x.txt");

move file and rename
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->move("Red/Hot/Chilli/Peppers/new/");

move only the files inside the directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->move("Red/Hot/Chilli/Peppers/new/");

move the folder and the files inside the directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->move("Red/Hot/Chilli/Peppers/new/",true);



listDir

If directory is selected the method returns a list of files and directories that are inside the directory. 

optitional parameters:

1. Recursive: Set to true if you also want to list the content of all subdirectories
2. Excluded extensions: Array of extension that you want to exclude from the result. For example array("jpg")
3. Excluded files: Array of files that you want to exclude from the result. For example array("Thumb.db")
4. Exclude directories: Array of directories you want to exclude from the result. For example array("backup", "temp")


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
$array = $fdh->listDir(); 
print "<pre>";
print_r($array);
print "</pre>";


simpleListDir

Equal than "listDir" metod returns a numeric array of files and directories that are inside the directory.
the array returned contains a list with the paths:

Array
(
    [0] => music/01 - Under the bridge.mp3
    [1] => music/02 - Give it away.mp3
    [2] => music/03 - Californication.mp3
    [16] => music/folder
)

$fdh = new fileDirHandler("music/");
$array = $fdh->simpleListDir(); 
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






zip functions



zipCreate

creates a zip file with the directory or files selected.
if not exist its created, it can also use exclude arrays to omit some files or directories.
also can receive a list of files to create the zip with them



parameters:

1. zipName: the name of the zip we attempt to create
2. overwrite: if the zip file exist will be overwritten for a new one otherwise the files wil be added (default false)
3. recursive: Set to true if you also want to list the content of all subdirectories (default true)
4. includeSelfFolder: if true creates the folder and content in the zip, otherwise only the files inside the folder will be added in destination zip (default false)
5. includeEmptyFolders: if true creates all the directories in the zip not matter if are empty (default true)
6. createOnPath: is a string path, if is defined the files will be added to this location on the zip file
7. fromFiles: is a list of files or directories, if is defined the function only take this files for adding.

Excluded options:
8. Excluded extensions: Array of extension that you want to exclude from the result. For example array("jpg")
9. Excluded files: Array of files that you want to exclude from the result. For example array("Thumb.db")
10. Exclude directories: Array of directories you want to exclude from the result. For example array("backup", "temp")


note: for "fromFiles" parameter the directories must end with "/" also applies for al zip functions

examples: 

//in this case, the zip include empty folders, and all content including subdirectories,if the zip file exist the files wil be added
//creates a zip with the content of the files in folder "music/"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip");

//to create a folder inside
//creates a zip with the content of the files in folder "music/"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip");

// in this case the files is overwritten by new one
$fdh = new fileDirHandler("image.png");
$fdh->zipCreate("test.zip",true);

// in this case we going to include in the zip the folder "music" and inside the files
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip",true,true,true);

// in this case we going to add all files inside tge folder "new" e.g.: "new/music/Otherside.mp3"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip",true,true,true,true,"new");

// in this case we going to add just 2 files and one directory with his content
$fdh = new fileDirHandler("music/");

//the files can be a string comma separated
$files = "image.png,music/Give it away.mp3,new/music/folder/";

//the files can be an array
$files = array();
$files[] = "image.png";
$files[] = "music/Give it away.mp3";
$files[] = "new/music/folder/";

$fdh->zipCreate("test.zip",true,true,true,true,"",$files);


// in this case we going to exclude the files with extensions mp3 and jpg
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip",true,true,true,true,"new",null,["mp3","jpg"]);




zipListing

this function reads all the entries in the zip file and returns an array of all files with information about it.
the useful data are:

name: full path of file in the zip
index: number of the file in the zip (be careful to use them, because it change if modify some file in zip)
type: can be "dir" or "file"
comment: the internal comment for this file


optitional parameters:

1. Excluded extensions: Array of extension that you want to exclude from the result. For example array("jpg")
2. Excluded files: Array of files that you want to exclude from the result. For example array("Thumb.db")
3. Exclude directories: Array of directories you want to exclude from the result. For example array("backup", "temp")


$fdh = new fileDirHandler("test.zip");
print "<pre>";
var_dump($fdh->zipListing() );
print "</pre>";

  [0]=>
  array(10) {
    ["name"]=> string(10) "new/music/"
    ["index"]=> int(0)
	["crc"]=> int(0)
	["size"]=> int(0)
    ["mtime"]=> int(1549086096)
	["comp_size"]=> int(0)
    ["comp_method"]=> int(0)
    ["encryption_method"]=> int(0)
    ["type"]=> string(3) "dir"
	["comment"]=> string(0) ""
  }



zipSimpleListing

Equal to zipListing, but it returns just a numeric array with te paths of files inside the zip.

$fdh = new fileDirHandler("test.zip");
print "<pre>";
var_dump($fdh->zipSimpleListing() );
print "</pre>";


array(3) {
  [0]=>  string(10) "new/music/"
  [1]=>  string(17) "new/music/folder/"
  [2]=>  string(27) "new/music/folder/readme.txt"
}

getZiFileInfo

return an array with the information of the file inside the zip, false if no found.
its useful for know if file exist in the zip or for get some data about the file, like a comment or so

parameters:

1. file: it can be the the path or the index in the zip file


$fdh = new fileDirHandler("test.zip");
print "<pre>";
var_dump($fdh->getZiFileInfo(0) );
var_dump($fdh->getZiFileInfo("new/music/folder/readme.txt") );
print "</pre>";



zipDelete


Erase files and directories recursively inside the zip


parameters:

1. files: a list of paths. it can be the the path or the index of the file or directory


in this example we delete some files, the list can be in diferents ways

$fdh = new fileDirHandler("test.zip");

$files = "new/music/ , new/music/folder/ , new/music/folder/readme.txt";

//or

$files = "0,1,2 ";

//or

$files = "new/music/ , 1 , new/music/folder/readme.txt";

//or

$files = array();
$files[] = 0;
$files[] = "new/music/folder/";
$files[] = 2;

//or

$files = $fdh->zipListing();// in this case we list all files in the zip, if we delete all files, the zip file "test.zip" is erased too

$fdh->zipDelete($files); 




zipAdd

Add files to an existing zip, is a shorcut to zipCreate with parameter "overwrite = false", 
if the zip file not exist will be created


1. zipName: the name of the zip we attempt to create
2. overwrite: if the zip file exist will be overwritten for a new one otherwise the files wil be added (default false)
3. recursive: Set to true if you also want to list the content of all subdirectories (default true)
4. includeSelfFolder: if true creates the folder and content in the zip, otherwise only the files inside the folder will be added in destination zip (default false)
5. includeEmptyFolders: if true creates all the directories in the zip not matter if are empty (default true)
6. createOnPath: is a string path, if is defined the files will be added to this location on the zip file
7. fromFiles: is a list of files or directories, if is defined the function only take this files for adding.

Excluded options:
8. Excluded extensions: Array of extension that you want to exclude from the result. For example array("jpg")
9. Excluded files: Array of files that you want to exclude from the result. For example array("Thumb.db")
10. Exclude directories: Array of directories you want to exclude from the result. For example array("backup", "temp")



//in this case, the zip include empty folders, and all content including subdirectories
//creates a zip with the content of the files in folder "music/"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip");



setZipComment

Saves a comment for the zip file, and also can make comments for an individual files or directories inside.
to see the comments in every file use the function "zipListing", for the general comment use "getZipComment"

parameters:

1. archiveComment: is the general comment in the file
2. files: is associative array with the file path and comment e.g $arr["filePath"] = "comment";


$fdh = new fileDirHandler("test.zip");

$filesComments = array();
$filesComments["new/music/"] = "root directory";
$filesComments["new/music/folder/"] = "info folder";
$filesComments["new/music/folder/readme.txt"] = "you must read it!!";

$fdh->setZipComment("Zip File comment",$filesComments);

print "<pre>";
var_dump($fdh->getZipComment() );
var_dump($fdh->zipListing() );
print "</pre>";




getZipComment

gets the general comment in the zip


$fdh = new fileDirHandler("test.zip");
echo($fdh->getZipComment() );



zipRead


Read and return the content of file inside a zip

parameters:

1. file: the path of file or index inside the zip

$fdh = new fileDirHandler("test.zip");
echo( $fdh->zipRead("new/music/folder/readme.txt") );
//or
echo( $fdh->zipRead(2) );



zipWrite

Write string content to file inside a zip, creates the file if not exist

parameters:

1. file: the path of file or index inside the zip
2. content: text to Write in the file
3. overwrite: if true replace the content with new one otherwise adds the text into the file (default false)


$fdh = new fileDirHandler("test.zip");
$fdh->zipWrite("new/music/folder/readme.txt","hello ");
//or
$fdh->zipWrite(2,"hello ");

$fdh->zipWrite("new/music/folder/readme.txt","overwritten",true);

echo($fdh->zipRead("new/music/folder/readme.txt"));






zipExtract

Extract the content of the entire zip file, or a list of files / directories


parameters:

1. destination: the path in the file system where the files will be extracted
2. files: optional list of paths to extract. it can be the the path or the index of the file or directory in the zip

// extract all files
$fdh = new fileDirHandler("test.zip");
$fdh->zipExtract("extract to here/other/and other/");


// extract a list of files
$fdh = new fileDirHandler("test.zip");
$fdh->zipExtract("extract to here/","new/music/,readme.txt,image.png");




zipCopy

copy a list of files to other path in the same zip and if is defined this function can copy the files to another zip file also

parameters:

1. files: list of paths. it can be the the path or the index of the file or directory in the zip
2. destination: the path in the zip where the files going to be copied
3. otherZipFile: if is defined and exist the copied files are added to "otherZipFile"

//copy files in the same zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipCopy("readme.txt","copied/");

//copy files in other file zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipCopy("new/music/ , readme.txt , image.png","copied/","nunevoZip.zip");



zipMove

move a list of files to other path in the same zip and if is defined this function can move the files to another zip file also

parameters:

1. files: list of paths. it can be the the path or the index of the file or directory in the zip
2. destination: the path in the zip where the files going to be copied
3. otherZipFile: if is defined and exist the copied files are added to "otherZipFile"

//move files in the same zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipMove("readme.txt","copied/");

//move files in other file zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipMove("new/music/ , readme.txt , image.png","copied/","nunevoZip.zip");







*/




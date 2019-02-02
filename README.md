# fileDirHandler

Simple PHP class to handle some useful functions for directory, file and zip compression


## set working Path
```
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/algo.txt");

//or

$fdh = new fileDirHandler();
$fdh->SetPath("Red/Hot/Chilli/Peppers/readme.txt");
```

## Write to a file

If want to create and write text to a file.
if the path doesn't exist it will be created
```
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/readme.txt");
$fdh->Write('hello world');// create if not exist

$fdh->Write('overwrite hello world',true);//overwrite previous content

$fdh->Write(', hello again');//add this content
```
parameters
1. string **contents**: text to write in file
2. bool **overwrite**:  defines if content is for add or rewrite the file (default false)


##  Read text from a file
you can get all content from a file.
```
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/readme.txt");
$content = $fdh->Read();
echo nl2br($content);
```

##  Create directory or file
You can create directories and files, with this method you also creates the parent directories if they doesn't exist.

parameters
1- bool **overwrite**: overwrite file if exist (default false)

```
//Create directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->Create();
```
```
//Create file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create();
```
```
//overwrite file if exist
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Create(true);
```

## Delete directory or file
be careful about permissions, if not the file or folder can't be deleted.

parameters
1. bool **onlyFilesInsideDir**: if true delete only the files and directories not the folder itself  (default false)

```
// delete a file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Delete();
```
```
// delete folder "Peppers" and its content
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->Delete();
```
```
// only delete the content of "Peppers" keeping the folder "Peppers"
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->Delete(true);
```

## Copy

copies the entire directory or file to other destination
if destination directory not exist is created


parameters
1. string **destination**: the path where we going to copy the files
2. bool **includeSelfFolder**: if true creates the folder and content in the destination, otherwise only the files inside the folder will be copied (default false)

```
copy file
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Copy("Red/Hot/Chilli/Peppers/new/Californication2.txt"); //also renames the file
```
```
copy directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Parallel universe/");
$fdh->Copy("Red/Hot/Chilli/Peppers/new/");
```
```
copy file in directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->Copy("Red/Hot/");
```

## Move

Similar to copy, but in this case the files are moved to the new destination
and can also use this function as directory an file renamer.
if destination directory not exist is created.

parameters
1. string **destination**: the path where we going to move the files
2. bool **includeSelfFolder**: if true creates the folder and content in the destination, otherwise only the files inside the folder will be copied (default false)

```
move file in a new directory with same name
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->move("Red/Hot/Chilli/Peppers/new/x.txt");
```
```
move file and rename
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/Californication.txt");
$fdh->move("Red/Hot/Chilli/Peppers/new/");
```
```
move only the files inside the directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->move("Red/Hot/Chilli/Peppers/new/");
```
```
move the folder and the files inside the directory
$fdh = new fileDirHandler("Red/Hot/Chilli/Peppers/");
$fdh->move("Red/Hot/Chilli/Peppers/new/",true);
```


## listDir

If directory is selected the method returns a list of files and directories that are inside the directory.

optitional parameters:
1. **Recursive**: Set to true if you also want to list the content of all subdirectories
2. **Excluded** extensions: Array of extension that you want to exclude from the result. For example array("jpg")
3. **Excluded** files: Array of files that you want to exclude from the result. For example array("Thumb.db")
4. **Exclude** directories: Array of directories you want to exclude from the result. For example array("backup", "temp")


the array returned contains an associative array with the file information:
```
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
```
in the case of directory:
```
[0] => Array
        (
            [type] => dir
            [parentDir] => music/
            [name] => folder
            [fullpath] => music/folder
        )
```
```
$fdh = new fileDirHandler("music/");
$array = $fdh->listDir();
print "<pre>";
print_r($array);
print "</pre>";
```


## simpleListDir

Equal than "listDir" metod returns a numeric array of files and directories that are inside the directory.
the array returned contains a list with the paths:
```
Array
(
    [0] => music/01 - Under the bridge.mp3
    [1] => music/02 - Give it away.mp3
    [2] => music/03 - Californication.mp3
    [16] => music/folder
)
```
```
$fdh = new fileDirHandler("music/");
$array = $fdh->simpleListDir();
print "<pre>";
print_r($array);
print "</pre>";
```


## getPath

this function return the selected path in the object

$fdh = new fileDirHandler("music/Universally speaking.mp3");
echo $fdh->getPath();


once you have set the path can access to some properties:

- **parentDir**: Parent directory's path
- **name**: name of the file or directory without extension
- **fileName**: name of the file or directory, if is file contain extension
- **extension**: file extension if has
- **mimeType**: Multipurpose Internet Mail Extensions,containing a type and a subtype in a string e.g. .jpg image is "image/jpeg"
- **type**: type of path directory or file (dir/file)
- **exist**: if the file or directory exist

```
$fdh = new fileDirHandler("music/13 - Universally  speaking.mp3");
echo $fdh->getPath()."<br>";
echo $fdh->parentDir."<br>";
echo $fdh->name."<br>";
echo $fdh->fileName."<br>";
echo $fdh->extension."<br>";
echo $fdh->mimeType."<br>";
echo $fdh->type."<br>";
echo $fdh->exist."<br>";
```
## mimeType fileDirHandler::mimeType();

you can get the MIME type (Multipurpose Internet Mail Extensions) of any file

if mime type is not found in a predefined list(691 common types), it can try to use PHP mime_content_type function, but file must exist.

```
echo fileDirHandler::getMimeType("By the way.mp3");

//returns "audio/mpeg"
```



## zip functions



## zipCreate

creates a zip file with the directory or files selected.
if not exist its created, it can also use exclude arrays to omit some files or directories.
also can receive a list of files to create the zip with them



parameters:
1. **zipName**: the name of the zip we attempt to create
2. **overwrite**: if the zip file exist will be overwritten for a new one otherwise the files wil be added (default false)
3. **recursive**: Set to true if you also want to list the content of all subdirectories (default true)
4. **includeSelfFolder**: if true creates the folder and content in the zip, otherwise only the files inside the folder will be added in destination zip (default false)
5. **includeEmptyFolders**: if true creates all the directories in the zip not matter if are empty (default true)
6. **createOnPath**: is a string path, if is defined the files will be added to this location on the zip file
7. **fromFiles**: is a list of files or directories, if is defined the function only take this files for adding.

Excluded options:
8. **Excluded extensions**: Array of extension that you want to exclude from the result. For example array("jpg")
9. **Excluded files**: Array of files that you want to exclude from the result. For example array("Thumb.db")
10. **Exclude directories**: Array of directories you want to exclude from the result. For example array("backup", "temp")


**note**: for "fromFiles" parameter the directories must end with "/" also applies for al zip functions

examples:
```
//in this case, the zip include empty folders, and all content including subdirectories,if the zip file exist the files wil be added
//creates a zip with the content of the files in folder "music/"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip");
```
```
//to create a folder inside
//creates a zip with the content of the files in folder "music/"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip");
```
```
// in this case the files is overwritten by new one
$fdh = new fileDirHandler("image.png");
$fdh->zipCreate("test.zip",true);
```
```
// in this case we going to include in the zip the folder "music" and inside the files
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip",true,true,true);
```
```
// in this case we going to add all files inside tge folder "new" e.g.: "new/music/Otherside.mp3"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip",true,true,true,true,"new");
```
```
// in this case we going to add just 2 files and one directory with his content
$fdh = new fileDirHandler("music/");
```
```
//the files can be a string comma separated
$files = "image.png,music/Give it away.mp3,new/music/folder/";

//the files can be an array
$files = array();
$files[] = "image.png";
$files[] = "music/Give it away.mp3";
$files[] = "new/music/folder/";

$fdh->zipCreate("test.zip",true,true,true,true,"",$files);
```
```
// in this case we going to exclude the files with extensions mp3 and jpg
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip",true,true,true,true,"new",null,["mp3","jpg"]);
```



## zipListing

this function reads all the entries in the zip file and returns an array of all files with information about it.
the useful data are:

**name**: full path of file in the zip
**index**: number of the file in the zip (be careful to use them, because it change if modify some file in zip)
**type**: can be "dir" or "file"
**comment**: the internal comment for this file


optitional parameters:
1. **Excluded extensions**: Array of extension that you want to exclude from the result. For example array("jpg")
2. **Excluded files**: Array of files that you want to exclude from the result. For example array("Thumb.db")
3. **Exclude directories**: Array of directories you want to exclude from the result. For example array("backup", "temp")

```
$fdh = new fileDirHandler("test.zip");
print "<pre>";
var_dump($fdh->zipListing() );
print "</pre>";
```
```
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
```


## zipSimpleListing

Equal to zipListing, but it returns just a numeric array with te paths of files inside the zip.

```
$fdh = new fileDirHandler("test.zip");
print "<pre>";
var_dump($fdh->zipSimpleListing() );
print "</pre>";
```
```
array(3) {
  [0]=>  string(10) "new/music/"
  [1]=>  string(17) "new/music/folder/"
  [2]=>  string(27) "new/music/folder/readme.txt"
}
```

## getZiFileInfo

return an array with the information of the file inside the zip, false if no found.
its useful for know if file exist in the zip or for get some data about the file, like a comment or so

parameters:
1. **file**: it can be the the path or the index in the zip file

```
$fdh = new fileDirHandler("test.zip");
print "<pre>";
var_dump($fdh->getZiFileInfo(0) );
var_dump($fdh->getZiFileInfo("new/music/folder/readme.txt") );
print "</pre>";
```


## zipDelete

Erase files and directories recursively inside the zip


parameters:
1. **files**: a list of paths. it can be the the path or the index of the file or directory


in this example we delete some files, the list can be in diferents ways

```
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
```

## zipAdd

Add files to an existing zip, is a shorcut to zipCreate with parameter "overwrite = false",
if the zip file not exist will be created


1. **zipName**: the name of the zip we attempt to create
2. **overwrite**: if the zip file exist will be overwritten for a new one otherwise the files wil be added (default false)
3. **recursive**: Set to true if you also want to list the content of all subdirectories (default true)
4. **includeSelfFolder**: if true creates the folder and content in the zip, otherwise only the files inside the folder will be added in destination zip (default false)
5. **includeEmptyFolders**: if true creates all the directories in the zip not matter if are empty (default true)
6. **createOnPath**: is a string path, if is defined the files will be added to this location on the zip file
7. **fromFiles**: is a list of files or directories, if is defined the function only take this files for adding.

Excluded options:
8. **Excluded extensions**: Array of extension that you want to exclude from the result. For example array("jpg")
9. **Excluded files**: Array of files that you want to exclude from the result. For example array("Thumb.db")
10. **Exclude directories**: Array of directories you want to exclude from the result. For example array("backup", "temp")


```
//in this case, the zip include empty folders, and all content including subdirectories
//creates a zip with the content of the files in folder "music/"
$fdh = new fileDirHandler("music/");
$fdh->zipCreate("test.zip");
```


## setZipComment

Saves a comment for the zip file, and also can make comments for an individual files or directories inside.
to see the comments in every file use the function "zipListing", for the general comment use "getZipComment"

parameters:
1. **archiveComment**: is the general comment in the file
2. **files**: is associative array with the file path and comment e.g $arr["filePath"] = "comment";

```
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
```

## getZipComment

gets the general comment in the zip

```
$fdh = new fileDirHandler("test.zip");
echo($fdh->getZipComment() );
```

## zipRead

Read and return the content of file inside a zip

parameters:
1. **file**: the path of file or index inside the zip

```
$fdh = new fileDirHandler("test.zip");
echo( $fdh->zipRead("new/music/folder/readme.txt") );
//or
echo( $fdh->zipRead(2) );
```


## zipWrite

Write string content to file inside a zip, creates the file if not exist

parameters:
1. **file**: the path of file or index inside the zip
2. **content**: text to Write in the file
3. **overwrite**: if true replace the content with new one otherwise adds the text into the file (default false)

```
$fdh = new fileDirHandler("test.zip");
$fdh->zipWrite("new/music/folder/readme.txt","hello ");
//or
$fdh->zipWrite(2,"hello ");

$fdh->zipWrite("new/music/folder/readme.txt","overwritten",true);

echo($fdh->zipRead("new/music/folder/readme.txt"));
```


## zipExtract

Extract the content of the entire zip file, or a list of files / directories


parameters:
1. **destination**: the path in the file system where the files will be extracted
2. **files**: optional list of paths to extract. it can be the the path or the index of the file or directory in the zip

```
// extract all files
$fdh = new fileDirHandler("test.zip");
$fdh->zipExtract("extract to here/other/and other/");
```
```
// extract a list of files
$fdh = new fileDirHandler("test.zip");
$fdh->zipExtract("extract to here/","new/music/,readme.txt,image.png");
```

## zipCopy

copy a list of files to other path in the same zip and if is defined this function can copy the files to another zip file also

parameters:
1. **files**: list of paths. it can be the the path or the index of the file or directory in the zip
2. **destination**: the path in the zip where the files going to be copied
3. **otherZipFile**: if is defined and exist the copied files are added to "otherZipFile"
```
//copy files in the same zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipCopy("readme.txt","copied/");
```
```
//copy files in other file zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipCopy("new/music/ , readme.txt , image.png","copied/","nunevoZip.zip");
```

## zipMove

move a list of files to other path in the same zip and if is defined this function can move the files to another zip file also

parameters:
1. **files**: list of paths. it can be the the path or the index of the file or directory in the zip
2. **destination**: the path in the zip where the files going to be copied
3. **otherZipFile**: if is defined and exist the copied files are added to "otherZipFile"
```
//move files in the same zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipMove("readme.txt","copied/");
```
```
//move files in other file zip
$fdh = new fileDirHandler("test.zip");
$fdh->zipMove("new/music/ , readme.txt , image.png","copied/","nunevoZip.zip");
```

<?php 
/*
* Template Name: action-ipo-delete-duplicate-media
*/


// Show a 4 second progress bar
echo '<br><br>Reloading in <span id="number">4</span>s: ';
echo '<div class="progress-bar" style="width: 100%; height: 20px; background-color: #ccc; position: relative; overflow: hidden;">
	<div class="progress-bar-inner" style="width: 0%; height: 100%; background-color: #0f0; position: absolute; top: 0; left: 0; transition: width 4s;"></div>
</div>';


echo '<br>start';

// Get wp uploads dir
$wp_upload_dir = wp_upload_dir();
$dir_to_process = $wp_upload_dir['basedir'] . '\2022\10';
echo '<br>uploads dir: '. $dir_to_process;

// Get all files in dir
$files = scandir($dir_to_process,1);

// Show only 2000
$files_sliced = array_slice($files, 0, 2000);

$modified_list = [];
$record_time_start = microtime(true);

$previous_file = false;
$previous_filesize = false;

$sizes = [
	'-768x337',
	'-300x132',
	'-150x150',
	'-1024x449',
	'-768x513',
	'-300x200',
	'-300x176',
	'-1024x684',

	'-600x352',
	'-600x263',
	'-750X440',
	'-1536x1025',
	'-2048x1367',
	'-600x320',
	'-300x222',
	'-1140-500',
	'-750-440',
	'-300x116',
	'-768x296',
	'-1536x592',
	'-271x300',
	'-300x71',
	'-768x182',
	'-1024x243',
];

foreach($files_sliced as $file){
	if($file == '.' || $file == '..') continue;

	
	$add_to_deletion_list = false;


	
	// Check if filename has any of the $sizes strings
	$has_size = false;
	foreach($sizes as $size){
		if(strpos($file, $size) !== false){
			$has_size = true;
			$add_to_deletion_list = true;
			break;
		}
	}

	// Remove extension of the file
	$file_no_ext = explode('.', $file)[0];

	// Remove the size string from the filename
	$file_no_size = str_replace($sizes, '', $file_no_ext);

	// Check if file_no_size ends with -{number}
	$number_part = explode('-', $file_no_size);



	if(end($number_part) != $number_part[0]){
		// This means that the file has a number at the end
		// Check if the number is 1-9



		$number = (int) end($number_part);
		if($number > 0 && $number < 100){



			$file_no_number = str_replace('-'.$number, '', $file_no_size);
			// Just make sure that the file is like the prev file, meaning it's his duplicate
			// file-1.jpg and file.jpg should return true
			if($previous_file){

				//if($file == '‏‏58821_summer_fevsitval_2022_1140-500-15.png'){
					//print_r($number_part);
					
					// Remove all unreadable characters
					$file_modified = preg_replace('/[^A-Za-z0-9\-]/', '', $file);
					$previous_file_modified = preg_replace('/[^A-Za-z0-9\-]/', '', $previous_file);

					$filesize = filesize($dir_to_process . '\\' . $file);
					$previous_filesize = filesize($dir_to_process . '\\' . $previous_file);


					//echo '<br>'.$file_modified;
					//echo '<br>'.$previous_file_modified;


					// Filenames are different, but size the same
					if($file_modified != $previous_file_modified && $filesize == $previous_filesize){
						$add_to_deletion_list = true;
					}
		
					//die();
				//}
				/*
				$previous_file_no_ext = explode('.', $previous_file)[0];
				if($previous_file_no_ext == $file_no_number){
					$add_to_deletion_list = true;
				}
				*/
			}
			
			
		}
		
	}

	if($add_to_deletion_list){
		$modified_list[] = $file;
	} else {
		$ok_list[] = $file;
	}

	$previous_file = $file;
}

echo '<br><br>deleted list: ';

// Delete all files from modified list
foreach($modified_list as $file){
	$file_path = $dir_to_process . '\\' . $file;

	unlink($file_path);

	// Check if file deleted
	if(file_exists($file_path)){
		//echo '<br>File not deleted: ' . $file_path;
	} else {
		//echo '<br>File deleted: ' . $file_path;
	}
}

$record_time_end = microtime(true);
$time_took = $record_time_end - $record_time_start;
$human_time = date('H:i:s', $time_took);

// Get new files count
$new_files = scandir($dir_to_process,1);
$fir_files_num = count($new_files);

echo '<br>time took: '. $human_time;
echo '<br>prev files in dir: '. count($files);
echo '<br>new files in dir: '. $fir_files_num;


//echo '<br>files: <pre>'. print_r($files,true).'</pre>';
//echo '<br>ok_list: <pre>'. print_r($ok_list,true).'</pre>';
//echo '<br>modified_list: <pre>'. print_r($modified_list,true).'</pre>';

// Load jquery from https://code.jquery.com/jquery-3.6.0.min.js using html
echo '<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>';



// When page loads, run jquery to change the progress bar width every second by 10%
/*
echo '<script>
	jQuery(document).ready(function(){

		var width = 0;
		var interval = setInterval(function(){
			width += 10;
			jQuery(".progress-bar-inner").css("width", width + "%");

			// Change #number content to show the amount of seconds left
			jQuery("#number").html((10 - (width/10)) + "");

			if(width >= 100){
				clearInterval(interval);
			}
		}, 1000);

		// When loading bar is 100%, reload the page
		jQuery(".progress-bar-inner").on("transitionend", function(){
			location.reload();
		});

	});

</script>';
*/
// When page loads, run jquery to change the progress bar width every second by 25%
echo '<script>
	jQuery(document).ready(function(){

		var width = 0;
		var interval = setInterval(function(){
			width += 25;
			jQuery(".progress-bar-inner").css("width", width + "%");

			// Change #number content to show the amount of seconds left
			jQuery("#number").html((5 - (width/25)) + "");

			if(width >= 100){
				clearInterval(interval);
			}
		}, 1000);

		// Reload the page after 4 seconds
		setTimeout(function(){
			location.reload();
		}, 4000);

	});
</script>';

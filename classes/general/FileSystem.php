<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) )	exit;

class FileSystem{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;
	
  private $use_filesystem = false;
  
  /******************************************/
	/***** class constructor **********/
	/******************************************/
  public function __construct(){

  }// construct function end here

  public function is_writeable($path = ABSPATH, $update_property = true)
  {
    $output = false;
    if (function_exists('request_filesystem_credentials')) {
      if(get_filesystem_method(array(), $path) === 'direct')
      {
        $creds = request_filesystem_credentials('', '', false, $path, array());
        if (WP_Filesystem($creds))
          $output = 'wp';
      }
    }
    if($output != 'wp'){
      if(wp_is_writable($path)){
        $output = true;
      }
    }
    if($update_property)
      $this->use_filesystem = $output;
    
    return $output;
  }
  
  /**
   * Attempts to use the correct path for the FS method being used
   *
   * @param string $abs_path
   *
   * @return string
   */
  public function get_sanitized_path($abs_path)
  {
    if($this->use_filesystem === 'wp'){
      global $wp_filesystem;
      return str_replace(ABSPATH, $wp_filesystem->abspath(), $abs_path);
    }
    return $abs_path;
  }

  /**
   * Create file if not exists then set mtime and atime on file
   *
   * @param string $abs_path
   * @param int    $time
   * @param int    $atime
   *
   * @return bool
   */
  public function touch($abs_path, $time = 0, $atime = 0)
  {
    if (0 == $time)
      $time = wp_date('U');
    
    if (0 == $atime)
      $atime = wp_date('U');

    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->touch($abs_path, $time, $atime);
      }
      else
        return @touch( $abs_path, $time, $atime );
    }

    return false;
  }


  /**
	 * file_put_contents with chmod
	 *
	 * @param string $abs_path
	 * @param string $contents
	 *
	 * @return bool
	 */
	public function put_contents( $abs_path, $contents ) {
    
    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->put_contents($abs_path, $contents);
      }
      else
        return @file_put_contents( $abs_path, $contents );
    }

    return false;
  }

  /**
	 * Get the contents of a file as a string
	 *
	 * @param string $abs_path
	 *
	 * @return string
	 */
	public function get_contents( $abs_path ) {

    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->get_contents($abs_path);
      }
      else
        return @file_get_contents( $abs_path );
    }

    return false;
  }


  /**
	 * Delete a file
	 *
	 * @param string $abs_path
	 *
	 * @return bool
	 */
	public function unlink( $abs_path ) {

    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->delete($abs_path, false, false);
      }
      else
        return @unlink( $abs_path );
    }
    return false;

  }

  /**
   * Is the specified path a directory?
   *
   * @param string $abs_path
   *
   * @return bool
   */
  public function is_dir($abs_path)
  {
    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->is_dir($abs_path);
      }
      else
        return is_dir( $abs_path );
    }
    return false;
  }

  /**
   * Is the specified path a file?
   *
   * @param string $abs_path
   *
   * @return bool
   */
  public function is_file($abs_path)
  {
    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->is_file($abs_path);
      }
      else
        return is_file( $abs_path );
    }
    return false;
  }

  /**
   * Is the specified path readable
   *
   * @param string $abs_path
   *
   * @return bool
   */
  public function is_readable($abs_path)
  {
    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->is_readable($abs_path);
      }
      else
        return is_readable( $abs_path );
    }
    return false;
  }

  /**
   * Recursive mkdir
   *
   * @param string $abs_path
   * @param int    $perms
   *
   * @return bool
   */
  public function mkdir($abs_path)
  {

    if ( $this->is_dir( $abs_path ) ) {
      return true;
    }

    $mkdirp = wp_mkdir_p( $abs_path );
    if($mkdirp)
      return true;

    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $this->wp_filesystem_mkdir( $abs_path);
      }
      else
        return mkdir( $abs_path );
    }
	}

  /**
	 * WP_Filesystem doesn't offer a recursive mkdir(), so this is that
	 *
	 * @param string   $abs_path
	 * @param int|null $perms
	 *
	 * @return string
	 */
	public function wp_filesystem_mkdir( $abs_path)
	{
    global $wp_filesystem;
		$abs_path = str_replace( '//', '/', $abs_path );
		$abs_path = rtrim( $abs_path, '/' );

		if ( empty( $abs_path ) ) {
			$abs_path = '/';
		}

		$dirs        = explode( '/', ltrim( $abs_path, '/' ) );
		$current_dir = '';

		foreach ( $dirs as $dir ) {
			$current_dir .= '/' . $dir;
			if ( !$this->is_dir( $current_dir ) ) {
				$wp_filesystem->mkdir( $current_dir);
			}
		}

		return $this->is_dir( $abs_path );
	}

  /**
   * Delete a directory
   *
   * @param string $abs_path
   * @param bool   $recursive
   *
   * @return bool
   */
  public function rmdir($abs_path, $recursive = false)
  {
    $return = false;
    if (!$this->is_dir($abs_path)) {
        return false;
    }

    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->rmdir($abs_path, $recursive);
      }
      else{
        // taken from WP_Filesystem_Direct
        if (!$recursive) {
          return @rmdir($abs_path);
        } else {
          // At this point it's a folder, and we're in recursive mode
          $abs_path = trailingslashit($abs_path);
          $filelist = $this->scandir($abs_path);

          $return = true;
          if (is_array($filelist)) {
            foreach ($filelist as $filename => $fileinfo) {
              if ('d' === $fileinfo['type']) {
                  $return = $this->rmdir($abs_path . $filename, $recursive);
              } else {
                  $return = $this->unlink($abs_path . $filename);
              }
            }
          }

          if (file_exists($abs_path) && !@rmdir($abs_path)) {
            $return = false;
          }
        }
      } 
    }
    return $return;
  }

  /**
   * Get a list of files/folders under specified directory
   *
   * @param string $abs_path
   * @param int $offset
   * @param int $limit
   * @param int $scan_count
   *
   * @return array|bool|\WP_error
   */
  public function scandir($abs_path, $offset = 0, $limit = -1, &$scan_count = 0)
  {
    if ($this->use_filesystem && $this->use_filesystem === 'wp') {
      global $wp_filesystem;
      $abs_path = $this->get_sanitized_path($abs_path);
      return $wp_filesystem->dirlist($abs_path, true, false);
    }

    $symlink = is_link($abs_path);
    $dirlist = @scandir($abs_path, SCANDIR_SORT_DESCENDING);

    if (false === $dirlist || empty($dirlist)) {
      return false;
    }

    if (-1 !== $limit) {
        $dirlist = array_slice($dirlist, $offset, $limit, true);
        $scan_count = count($dirlist);
    }

    $return = array();

    // normalize return to look somewhat like the return value for WP_Filesystem::dirlist
    foreach ($dirlist as $entry) {
      if ('.' === $entry || '..' === $entry) {
          continue;
      }
      $return[$entry] = $this->get_file_info($entry, $abs_path, $symlink);
    }
    return $return;
  }

  /**
   * Converts file paths that include mixed slashes to use the correct type of slash for the current operating system.
   *
   * @param $path string
   *
   * @return string
   */
  public function slash_one_direction($path)
  {
    return str_replace(array('/', '\\'), DIRECTORY_SEPARATOR, $path);
  }

  /**
   * Get a file's size
   *
   * @param string $abs_path
   *
   * @return int
   */
  public function filesize($abs_path)
  {
    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $abs_path = $this->get_sanitized_path($abs_path);
        return $wp_filesystem->size($abs_path);
      }
      else
        return filesize( $abs_path );
    }
    return false;
  }

  /**
   * @param string $entry
   * @param string $abs_path
   * @param bool   $symlink
   *
   * @return array
   */
  public function get_file_info($entry, $abs_path, $symlink = false )
  {
      $abs_path  = $this->slash_one_direction($abs_path);
      $full_path = trailingslashit($abs_path) . $entry;
      $real_path = realpath($full_path); // Might be different due to symlinks.

      $upload_info     = wp_get_upload_dir();
      $uploads_basedir = $upload_info['basedir'];
      $uploads_folder  = wp_basename($uploads_basedir);
      $is_uploads_in_content     = strpos($uploads_basedir, WP_CONTENT_DIR);
      $content_path              = false !== $is_uploads_in_content ? WP_CONTENT_DIR : dirname($uploads_basedir);
      $return                    = array();
      $return['name']            = $entry;
      $return['relative_path']   = str_replace($abs_path, '', $full_path);
      $return['wp_content_path'] = str_replace($this->slash_one_direction($content_path) . DIRECTORY_SEPARATOR, '', $full_path);
      $return['absolute_path']   = $full_path;
      $return['type']            = $this->is_dir($abs_path . DIRECTORY_SEPARATOR . $entry) ? 'd' : 'f';
      $return['size']            = $this->filesize($abs_path . DIRECTORY_SEPARATOR . $entry);
      $return['lastmodunix']       = filemtime($abs_path . DIRECTORY_SEPARATOR . $entry);

      if ($symlink) {
          $return['subpath'] = DIRECTORY_SEPARATOR . basename(dirname($real_path)) . DIRECTORY_SEPARATOR . $entry;
      } else {
          $return['subpath'] = preg_replace("#^(themes|plugins|{$uploads_folder})#", '', $return['wp_content_path']);
      }

      $exploded              = explode(DIRECTORY_SEPARATOR, $return['subpath']);
      $return['folder_name'] = isset($exploded[1]) ? $exploded[1] : $return['relative_path'];

      return $return;
  }

  /******************************************/
  /***** download_url function start from here *********/
  /******************************************/
  public function download_url($url){
    if ( ! function_exists( 'download_url' )) {
      require_once ABSPATH . 'wp-admin/includes/file.php';
    }
    $tmp_file = download_url($url);
    return $tmp_file;
  }

  /******************************************/
  /***** unzip_file function start from here *********/
  /******************************************/
  public function unzip_file($file, $to = ABSPATH, $delete_package = false){
    $output = false;
    if($this->use_filesystem){
      if($this->use_filesystem === 'wp'){
        global $wp_filesystem;
        $output = unzip_file($file, $to);
      }else{
        $Zip = Zip::instance();
        $output = $Zip->unzip_file($file, $to);
      }
    }
    if($delete_package)
      $this->unlink($file);
    return $output;
  }

} // BBWP_CustomFields class


<?php
namespace OTW\GeneralWooRingBuilder;

if ( ! defined( 'ABSPATH' ) )	exit;

class Zip{

  use \OTW\GeneralWooRingBuilder\Traits\Singleton;

  /******************************************/
	/***** class constructor **********/
	/******************************************/
  public function __construct(){
    
  }// construct function end here


  /******************************************/
	/***** unzip_file **********/
	/******************************************/
  public function unzip_file( $file, $to ) {
    
    // Unzip can use a lot of memory, but not this much hopefully.
    wp_raise_memory_limit( 'admin' );
 
    $needed_dirs = array();
    $to          = trailingslashit( $to );
 
    $FileSystem = FileSystem::instance();
    $FileSystem->is_writeable($to);
    
    // Determine any parent directories needed (of the upgrade directory).
    if ( ! $FileSystem->is_dir( $to ) ) { // Only do parents if no children exist.
        $path = preg_split( '![/\\\]!', untrailingslashit( $to ) );
        for ( $i = count( $path ); $i >= 0; $i-- ) {
            if ( empty( $path[ $i ] ) ) {
                continue;
            }
 
            $dir = implode( '/', array_slice( $path, 0, $i + 1 ) );
            if ( preg_match( '!^[a-z]:$!i', $dir ) ) { // Skip it if it looks like a Windows Drive letter.
                continue;
            }
 
            if ( ! $FileSystem->is_dir( $dir ) ) {
                $needed_dirs[] = $dir;
            } else {
                break; // A folder exists, therefore we don't need to check the levels below this.
            }
        }
    }
 
    /**
     * Filters whether to use ZipArchive to unzip archives.
     *
     * @since 3.0.0
     *
     * @param bool $ziparchive Whether to use ZipArchive. Default true.
     */
    if ( class_exists( 'ZipArchive', false )) {
        $result = $this->_unzip_file_ziparchive( $file, $to, $needed_dirs );
        if ( true === $result ) {
            return $result;
        } elseif ( is_wp_error( $result ) ) {
            if ( 'incompatible_archive' !== $result->get_error_code() ) {
                return $result;
            }
        }
    }
    // Fall through to PclZip if ZipArchive is not available, or encountered an error opening the file.
    return $this->_unzip_file_pclzip( $file, $to, $needed_dirs );
    
  }

  /******************************************/
	/***** _unzip_file_ziparchive **********/
	/******************************************/
  function _unzip_file_ziparchive( $file, $to, $needed_dirs = array() ) {
      
    $FileSystem = FileSystem::instance();
    $FileSystem->is_writeable($to);

      $z = new \ZipArchive();
  
      $zopen = $z->open( $file, \ZIPARCHIVE::CHECKCONS );
  
      if ( true !== $zopen ) {
          return new \WP_Error( 'incompatible_archive', __( 'Incompatible Archive.' ), array( 'ziparchive_error' => $zopen ) );
      }
  
      $uncompressed_size = 0;
  
      for ( $i = 0; $i < $z->numFiles; $i++ ) {
          $info = $z->statIndex( $i );
  
          if ( ! $info ) {
              return new \WP_Error( 'stat_failed_ziparchive', __( 'Could not retrieve file from archive.' ) );
          }
  
          if ( '__MACOSX/' === substr( $info['name'], 0, 9 ) ) { // Skip the OS X-created __MACOSX directory.
              continue;
          }
  
          // Don't extract invalid files:
          if ( 0 !== validate_file( $info['name'] ) ) {
              continue;
          }
  
          $uncompressed_size += $info['size'];
  
          $dirname = dirname( $info['name'] );
  
          if ( '/' === substr( $info['name'], -1 ) ) {
              // Directory.
              $needed_dirs[] = $to . untrailingslashit( $info['name'] );
          } elseif ( '.' !== $dirname ) {
              // Path to a file.
              $needed_dirs[] = $to . untrailingslashit( $dirname );
          }
      }
  
      /*
      * disk_free_space() could return false. Assume that any falsey value is an error.
      * A disk that has zero free bytes has bigger problems.
      * Require we have enough space to unzip the file and copy its contents, with a 10% buffer.
      */
      if ( wp_doing_cron() ) {
          $available_space = @disk_free_space( WP_CONTENT_DIR );
  
          if ( $available_space && ( $uncompressed_size * 2.1 ) > $available_space ) {
              return new \WP_Error(
                  'disk_full_unzip_file',
                  __( 'Could not copy files. You may have run out of disk space.' ),
                  compact( 'uncompressed_size', 'available_space' )
              );
          }
      }
  
      $needed_dirs = array_unique( $needed_dirs );
  
      foreach ( $needed_dirs as $dir ) {
          // Check the parent folders of the folders all exist within the creation array.
          if ( untrailingslashit( $to ) === $dir ) { // Skip over the working directory, we know this exists (or will exist).
              continue;
          }
  
          if ( strpos( $dir, $to ) === false ) { // If the directory is not within the working directory, skip it.
              continue;
          }
  
          $parent_folder = dirname( $dir );
  
          while ( ! empty( $parent_folder )
              && untrailingslashit( $to ) !== $parent_folder
              && ! in_array( $parent_folder, $needed_dirs, true )
          ) {
              $needed_dirs[] = $parent_folder;
              $parent_folder = dirname( $parent_folder );
          }
      }
  
      asort( $needed_dirs );
  
      // Create those directories if need be:
      foreach ( $needed_dirs as $_dir ) {
          // Only check to see if the Dir exists upon creation failure. Less I/O this way.
          if ( ! $FileSystem->mkdir( $_dir) && ! $FileSystem->is_dir( $_dir ) ) {
              return new \WP_Error( 'mkdir_failed_ziparchive', __( 'Could not create directory.' ), substr( $_dir, strlen( $to ) ) );
          }
      }
      unset( $needed_dirs );
  
      for ( $i = 0; $i < $z->numFiles; $i++ ) {
          $info = $z->statIndex( $i );
  
          if ( ! $info ) {
              return new \WP_Error( 'stat_failed_ziparchive', __( 'Could not retrieve file from archive.' ) );
          }
  
          if ( '/' === substr( $info['name'], -1 ) ) { // Directory.
              continue;
          }
  
          if ( '__MACOSX/' === substr( $info['name'], 0, 9 ) ) { // Don't extract the OS X-created __MACOSX directory files.
              continue;
          }
  
          // Don't extract invalid files:
          if ( 0 !== validate_file( $info['name'] ) ) {
              continue;
          }
  
          $contents = $z->getFromIndex( $i );
  
          if ( false === $contents ) {
              return new \WP_Error( 'extract_failed_ziparchive', __( 'Could not extract file from archive.' ), $info['name'] );
          }
  
          if ( ! $FileSystem->put_contents( $to . $info['name'], $contents) ) {
              return new \WP_Error( 'copy_failed_ziparchive', __( 'Could not copy file.' ), $info['name'] );
          }
      }
  
      $z->close();
  
      return true;
  }

  /******************************************/
	/***** _unzip_file_ziparchive **********/
	/******************************************/
  function _unzip_file_pclzip( $file, $to, $needed_dirs = array() ) {

    $FileSystem = FileSystem::instance();
    $FileSystem->is_writeable($to);
 
    mbstring_binary_safe_encoding();
 
    require_once ABSPATH . 'wp-admin/includes/class-pclzip.php';
 
    $archive = new \PclZip( $file );
 
    $archive_files = $archive->extract( PCLZIP_OPT_EXTRACT_AS_STRING );
 
    reset_mbstring_encoding();
 
    // Is the archive valid?
    if ( ! is_array( $archive_files ) ) {
        return new \WP_Error( 'incompatible_archive', __( 'Incompatible Archive.' ), $archive->errorInfo( true ) );
    }
 
    if ( 0 === count( $archive_files ) ) {
        return new \WP_Error( 'empty_archive_pclzip', __( 'Empty archive.' ) );
    }
 
    $uncompressed_size = 0;
 
    // Determine any children directories needed (From within the archive).
    foreach ( $archive_files as $file ) {
        if ( '__MACOSX/' === substr( $file['filename'], 0, 9 ) ) { // Skip the OS X-created __MACOSX directory.
            continue;
        }
 
        $uncompressed_size += $file['size'];
 
        $needed_dirs[] = $to . untrailingslashit( $file['folder'] ? $file['filename'] : dirname( $file['filename'] ) );
    }
 
    /*
     * disk_free_space() could return false. Assume that any falsey value is an error.
     * A disk that has zero free bytes has bigger problems.
     * Require we have enough space to unzip the file and copy its contents, with a 10% buffer.
     */
    if ( wp_doing_cron() ) {
        $available_space = @disk_free_space( WP_CONTENT_DIR );
 
        if ( $available_space && ( $uncompressed_size * 2.1 ) > $available_space ) {
            return new \WP_Error(
                'disk_full_unzip_file',
                __( 'Could not copy files. You may have run out of disk space.' ),
                compact( 'uncompressed_size', 'available_space' )
            );
        }
    }
 
    $needed_dirs = array_unique( $needed_dirs );
 
    foreach ( $needed_dirs as $dir ) {
        // Check the parent folders of the folders all exist within the creation array.
        if ( untrailingslashit( $to ) === $dir ) { // Skip over the working directory, we know this exists (or will exist).
            continue;
        }
 
        if ( strpos( $dir, $to ) === false ) { // If the directory is not within the working directory, skip it.
            continue;
        }
 
        $parent_folder = dirname( $dir );
 
        while ( ! empty( $parent_folder )
            && untrailingslashit( $to ) !== $parent_folder
            && ! in_array( $parent_folder, $needed_dirs, true )
        ) {
            $needed_dirs[] = $parent_folder;
            $parent_folder = dirname( $parent_folder );
        }
    }
 
    asort( $needed_dirs );
 
    // Create those directories if need be:
    foreach ( $needed_dirs as $_dir ) {
        // Only check to see if the dir exists upon creation failure. Less I/O this way.
        if ( ! $FileSystem->mkdir( $_dir) && ! $FileSystem->is_dir( $_dir ) ) {
            return new \WP_Error( 'mkdir_failed_pclzip', __( 'Could not create directory.' ), substr( $_dir, strlen( $to ) ) );
        }
    }
    unset( $needed_dirs );
 
    // Extract the files from the zip.
    foreach ( $archive_files as $file ) {
        if ( $file['folder'] ) {
            continue;
        }
 
        if ( '__MACOSX/' === substr( $file['filename'], 0, 9 ) ) { // Don't extract the OS X-created __MACOSX directory files.
            continue;
        }
 
        // Don't extract invalid files:
        if ( 0 !== validate_file( $file['filename'] ) ) {
            continue;
        }
 
        if ( ! $FileSystem->put_contents( $to . $file['filename'], $file['content']) ) {
            return new \WP_Error( 'copy_failed_pclzip', __( 'Could not copy file.' ), $file['filename'] );
        }
    }
 
    return true;
  }
}
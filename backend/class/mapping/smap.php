<?php namespace codename\core\io;
use codename;
use \Exception;
use \codename\core\value;
use \codename\core\exception;

/**
 * single mapping
 * @var [type]
 */
class smap extends \codename\core\io\transform
{

    /**
     * @inheritDoc
     */
    public function map()
    {

    }
    /**
     * [protected description]
     * @var value
     */
    protected $source = null;

    /**
     * [protected description]
     * @var value
     */
    protected $target = null;


    protected $sourceValidator = null; //validates datatype in source


    protected $targetValidator = null; //validates datatype in target

    /*
    protected $sourcedataValidator = null; //validates data in source
    protected $targetdataValidator = null; //validates data in target
    */

    /**
     * [protected description]
     * @var cast
     */
    protected $cast = null;      //casts datatypes when needed

    /**
     * [protected description]
     * @var [type]
     */
    protected $errorhandler = null; //handles cast errors

    /**
     * returns true if source value could be maped (cast) to target value
     * @return bool [description]
     */
    public function mapable():bool
    {
       //1.a determine validator for source
       //1.b determine validator for target
       //2. validate datatypes and data of source and target
       if(count($errors = \codename\core\app::getValidator($this->sourcevalidator)->reset()->validate($source)) > 0)
          {
              throw new exception('MAPABLE_ERROR_SOURCE_DATA_INVALID', exception::$ERRORLEVEL_ERROR);
          }
       if(count($errors = \codename\core\app::getValidator($this->targetvalidator)->reset()->validate($target)) > 0)
          {
              throw new exception('MAPABLE_ERROR_TARGET_DATA_INVALID', exception::$ERRORLEVEL_ERROR);
          }
    }

    // really do the mapping, copy value from target to source
    public function map():bool
    {
        if (!$this->mapable()){
              return false;
        }

        //cast
        if (!is_null($cast)){
            $this->source = $cast->cast();
        }
        else {
          $this->source = $target;
        }
    }


    public function transform()
    {
        $this->map();
    }

    /**
     * returns true if the transform has allready been done
     */
    public function IsTransformed():bool
    {

      throw new \codename\core\exception('Not implemented yet!', exception::$ERRORLEVEL_ERROR);
      
    }
}
 ?>

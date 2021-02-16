<?php
namespace codename\core\io\target;

use SetaPDF_FormFiller_Field_FieldInterface;

use \codename\core\app;

// use \codename\setapdf\Autoload;

// require_once( app::getHomedir('codename', 'core-io') . 'lib/SetaPDF/Autoload.php' );

/**
 * pdfform as a target
 */
class pdfform extends \codename\core\io\target {

  protected $inputFilePath = null;
  protected $outputFilePath = null;

  /**
   * @param string  $name
   * @param array   $config
   */
  public function __construct(string $name, array $config)
  {
    parent::__construct($name, $config);
    $this->inputFilePath = $config['inputfile'];
    $this->outputFilePath = $config['outputfile'];
    $this->flatten = $config['flatten'] ?? false;
    $this->flattenMode = $config['flatten_mode'] ?? null;
    $this->flattenExclude = $config['flatten_exclude'] ?? [];
  }

  /**
   * flattens ALL fields
   * @var string
   */
  const FLATTEN_MODE_ALL = 'all';

  /**
   * flattens all set fields
   * @var string
   */
  const FLATTEN_MODE_SET = 'set';

  /**
   * specific mode for flattening fields
   * @var string
   */
  protected $flattenMode = self::FLATTEN_MODE_SET;

  /**
   * [protected description]
   * @var bool
   */
  protected $flatten = false;

  /**
   * Array of PDF Object IDs to exclude from flattening process
   * @var string[]
   */
  protected $flattenExclude = [];

  /**
   * [setFlattenExclude description]
   * @param array $fields [description]
   */
  public function setFlattenExclude(array $fields) {
    $this->flattenExclude = $fields;
  }

  /**
   * @inheritDoc
   */
  public function store(array $data) : bool
  {
    // setup a document instance
    $writer = new \SetaPDF_Core_Writer_File($this->outputFilePath, true);
    $document = \SetaPDF_Core_Document::loadByFilename(
        $this->inputFilePath, $writer
    );
    // get a form filler instance
    $formFiller = new \SetaPDF_FormFiller($document);
    $xfa = $formFiller->getXfa();
    if ($xfa === false) {

    } else {
      die("XFA FORM!");
    }

    $fields = $formFiller->getFields();

    $flattenFieldsByNames = [];

    // DEBUG
    $exportValues = [];

    foreach($fields as $fieldName => $field) {

      // DEBUG
      if(method_exists($field, 'getExportValue')) {
        $exportValues[$fieldName] = $field->getExportValue();
      }

      if(isset($data[$fieldName])) {
        //
        // via field name
        //

        if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
          app::getResponse()->setData('pdfform_field_type>'.$fieldName, gettype($data[$fieldName]));
        }

        // files/images
        if($data[$fieldName] instanceof \SplFileObject) {
          $file = $data[$fieldName];
          if($file instanceof \SplFileObject) {
            // $fileInfo = $file->getFileInfo();
            // $fileInfo->getExtension()

            if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
              app::getResponse()->setData('pdfform_field_file>'.$fieldObject->getObjectId(), true);
              app::getResponse()->setData('pdfform_field_file_path>'.$fieldObject->getObjectId(), $file->getRealPath());
            }

            if($field instanceof \SetaPDF_FormFiller_Field_AbstractField) {
              $annotation = $field->getAnnotation();
              $width = $annotation->getWidth();
              $height = $annotation->getHeight();

              // Create a form xobject to which we are going to write the image.
              // This form xobject will be the resulting appearance of our form field.
              $xObject = \SetaPDF_Core_XObject_Form::create($document, [0, 0, $width, $height]);
              // Get the canvas for this xobject
              $canvas = $xObject->getCanvas();

              $image = \SetaPDF_Core_Image::getByPath($file->getRealPath());
              $image = $image->toXObject($document);

              // Let's fit and center the image in the field area:
              if ($image->getHeight($width) >= $height) {
                  $image->draw(
                      $canvas, $width / 2 - $image->getWidth($height) / 2, 0, null, $height
                  );
              } else {
                  $image->draw(
                      $canvas, 0, $height / 2 - $image->getHeight($width) / 2, $width
                  );
              }

              if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
                app::getResponse()->setData('pdfform_field_file_props>'.$fieldObject->getObjectId(), [
                  'width' => $width,
                  'height' => $height,
                  'res_type' => $image->getResourceType()
                ]);
              }

              // Now add the appearance to the annotation
              $annotation->setAppearance($xObject);
              $flattenFieldsByNames[] = $field->getQualifiedName();

              continue;
            }
          }
        }


        //
        // Handle Buttons in Button Groups
        //
        if($field instanceof \SetaPDF_FormFiller_Field_ButtonGroup) {
          $buttons = $field->getButtons();
          foreach($buttons as $btn) {
            $fieldObject = $btn->getFieldObject();
            if(isset($data[$fieldObject->getObjectId()])) {
              //
              // Use a boolean for button values?
              //
              $btn->setValue($data[$fieldObject->getObjectId()]);

              // Add button (by field fqn) to the to-be-flattened-fields
              $flattenFieldsByNames[] = $btn->getQualifiedName();
            }
          }
          // Add button group (by field fqn) to the to-be-flattened-fields
          $flattenFieldsByNames[] = $field->getQualifiedName();

        //
        // NOTE: see below, same check for class of "SetaPDF_FormFiller_Field_Button"
        //
        // } else if($field instanceof \SetaPDF_FormFiller_Field_Button) {
        //
        //   if(isset($data[$fieldName])) {
        //     $field->setValue($data[$fieldObject->getObjectId()]);
        //   //   $field->push();
        //   // } else {
        //   //   $field->pull();
        //   }
        //
        // } else if($field instanceof \SetaPDF_FormFiller_Field_PushButton) {
        //
        //   app::getResponse()->setData('pdfform_debug_not_possible_pushbutton_', $field->getName());

        } else {
          //
          // handle regular fields (Tx)
          //
          $field->setValue($data[$fieldName]);

          if(empty($this->flattenExclude)
            || !in_array($field->getFieldObject->getObjectId(), $this->flattenExclude)
          ) {

          }
          // Add field (by field fqn) to the to-be-flattened-fields
          $flattenFieldsByNames[] = $field->getQualifiedName();

          continue;

        }
      } else {
        //
        // Via object id
        //

        $fieldObject = null;
        // NOTE: ->getFieldObject() is only valid for non-buttongroups...
        if(!($field instanceof \SetaPDF_FormFiller_Field_ButtonGroup)) {
          $fieldObject = $field->getFieldObject();
        }

        if($fieldObject && isset($data[$fieldObject->getObjectId()])) {

          // files/images
          if($data[$fieldObject->getObjectId()] instanceof \SplFileObject) {
            $file = $data[$fieldObject->getObjectId()];
            if($file instanceof \SplFileObject) {
              // $fileInfo = $file->getFileInfo();
              // $fileInfo->getExtension()

              if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
                app::getResponse()->setData('pdfform_field_file>'.$fieldObject->getObjectId(), true);
                app::getResponse()->setData('pdfform_field_file_path>'.$fieldObject->getObjectId(), $file->getRealPath());
              }

              if($field instanceof \SetaPDF_FormFiller_Field_AbstractField) {
                $annotation = $field->getAnnotation();
                $width = $annotation->getWidth();
                $height = $annotation->getHeight();

                // Create a form xobject to which we are going to write the image.
                // This form xobject will be the resulting appearance of our form field.
                $xObject = \SetaPDF_Core_XObject_Form::create($document, [0, 0, $width, $height]);
                // Get the canvas for this xobject
                $canvas = $xObject->getCanvas();

                $image = \SetaPDF_Core_Image::getByPath($file->getRealPath());
                $image = $image->toXObject($document);

                // Let's fit and center the image in the field area:
                if ($image->getHeight($width) >= $height) {
                    $image->draw(
                        $canvas, $width / 2 - $image->getWidth($height) / 2, 0, null, $height
                    );
                } else {
                    $image->draw(
                        $canvas, 0, $height / 2 - $image->getHeight($width) / 2, $width
                    );
                }

                if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
                  app::getResponse()->setData('pdfform_field_file_props>'.$fieldObject->getObjectId(), [
                    'width' => $width,
                    'height' => $height,
                    'res_type' => $image->getResourceType()
                  ]);
                }

                // Now add the appearance to the annotation
                $annotation->setAppearance($xObject);
                $flattenFieldsByNames[] = $field->getQualifiedName();
                continue;
              }
            }
          }
        }

        //
        // Handle Buttons in Button Groups
        //
        if($field instanceof \SetaPDF_FormFiller_Field_ButtonGroup) {
            $buttons = $field->getButtons();
            foreach($buttons as $btn) {
              $fieldObject = $btn->getFieldObject();
              if(isset($data[$fieldObject->getObjectId()])) {

                if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
                  app::getResponse()->setData('pdfform_checked_objids>'.$fieldObject->getObjectId(), true);
                }
                //
                // Use a boolean for button values?
                //
                $btn->setValue($data[$fieldObject->getObjectId()]);

                // Add button (by field fqn) to the to-be-flattened-fields
                $flattenFieldsByNames[] = $btn->getQualifiedName();
              }
            }
            $flattenFieldsByNames[] = $field->getQualifiedName();

        //
        // NOTE: the following code is just for testing singular buttons
        // it seems a bit strange, they sometimes occur singularly?
        //
        // } else if($field instanceof \SetaPDF_FormFiller_Field_Button) {
        //
        //   app::getResponse()->setData('pdfform_checked_buttons>'.$fieldObject->getObjectId(), $data[$field->getFieldObject()->getObjectId()] ?? null);
        //
        //   if(isset($data[$field->getFieldObject()->getObjectId()])) {
        //     $field->setValue($data[$fieldObject->getObjectId()]);
        //     // if($data[$field->getFieldObject()->getObjectId()]) {
        //     //   $field->push();
        //     // } else {
        //     //   $field->pull();
        //     // }
        //   }

        } else {
          //
          // Handle regular fields (Tx)
          //
          $fieldObject = $field->getFieldObject();

          $flattenFieldsByNames[] = $field->getQualifiedName();

        }
      }
    }


    // DEBUG
    if(\codename\core\app::getAuth()->isAuthenticated() && \codename\core\app::getAuth()->memberOf('debug')) {
      app::getResponse()->setData('pdfform', $fields->getNames());
      app::getResponse()->setData('export_values', $exportValues);
    }


    if($this->flatten) {

      // if($this->flattenMode === self::FLATTEN_MODE_SET) {
      foreach($flattenFieldsByNames as $fieldName) {
        if($fields->offsetExists($fieldName)) {
          if($field = $fields->get($fieldName)) {

            // Partial flattening - check for PDF object IDs
            if($field instanceof \SetaPDF_FormFiller_Field_AbstractField) {
              $fieldObject = $field->getFieldObject();
              if(in_array($fieldObject->getObjectId(), $this->flattenExclude)) {
                continue;
              }
            }
            $field->flatten();
          }
        }
      }

      if($this->flattenMode === self::FLATTEN_MODE_ALL) {

        // Track to-be-flattened field instances
        // (only needed for partial flatten)
        $flattenFieldInstances = [];

        foreach($fields as $name => $field) {

          if($field instanceof \SetaPDF_FormFiller_Field_AbstractField) {
            $fieldObject = $field->getFieldObject();
            if(in_array($fieldObject->getObjectId(), $this->flattenExclude)) {
              // Exclude field by PDF Object ID
              continue;
            } else {
              $flattenFieldInstances[] = $field;
            }
          }
        }

        // only flatten all, if no exclusions defined
        if(empty($this->flattenExclude)) {
          $fields->flatten();
        } else {
          foreach($flattenFieldInstances as $fieldInstance) {
            $fields->flatten($fieldInstance);
          }
        }
      }
    }

    $document->save()->finish();

    // store data in a pdf
    return true;
  }

  /**
   * @inheritDoc
   */
  public function finish()
  {
    return;
  }

}

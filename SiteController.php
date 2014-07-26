<?php
/**
 * @package   ImpressPages
 */
namespace Plugin\FileUploadExample;

/**
 * Created by PhpStorm.
 * User: mangirdas
 * Date: 7/25/14
 * Time: 5:34 PM
 */

class SiteController
{


    /**
     * Display file upload form and two additional input fields just for fun.
     * @return string
     */
    public function uploadForm()
    {
        $form = new \Ip\Form();

        //this hidden input field instructs ImpressPages to submit form to 'myUploadHandler' method which is declared on this file.
        $form->addField(new \Ip\Form\Field\Hidden(
                array(
                    'name' => 'sa',
                    'value' => 'FileUploadExample.myUploadHandler'
                )
            )
        );


        //this text input is just for fun. As empty form with just file upload looks strange.
        $form->addField(new \Ip\Form\Field\Text(
                array(
                    'name' => 'text',
                    'label' => 'Some text input'
                )
            )
        );

        //file upload field
        $form->addField(new \Ip\Form\Field\File(
                array(
                    'name' => 'importantDocuments',
                    'label' => 'Add some files'
                )
            )
        );

        //this text input is just for fun. As empty form with just file upload looks strange.
        $form->addField(new \Ip\Form\Field\Textarea(
                array(
                    'name' => 'textarea',
                    'label' => 'Some textarea'
                )
            )
        );

        $form->addField(new \Ip\Form\Field\Submit(
                array(
                    'value' => 'Submit'
                )
            )
        );

        return $form->render();

    }


    /**
     * Handle form submit and store uploaded files
     * @return \Ip\Response\Json
     * @throws \Ip\Exception
     */
    public function myUploadHandler()
    {
        ipRequest()->mustBePost();//assure to allow only POST requests to improve the security.

        $info = '';

        $uploadedFileData = ipRequest()->getPost("importantDocuments");
        if (!empty($uploadedFileData)) {
            $files  = $uploadedFileData['file'];
            $originalFileNames = $uploadedFileData['originalFileName'];

            $info .= "<h3>Uploaded files into file/secure/tmp dir:</h3> " . implode("<br/>", $files);
            $info .= "<h3>Original names of files when they were on user's computer:</h3> " . implode("<br/>", $originalFileNames);


            //we will copy files from file/secure/tmp to file/secure/myCustomDir for permantent storage
            //BE CAREFUL!!!!!!!!!!
            //Moving uploaded files outside secure directory is very dangerous.
            //You have to check if file can't harm your server before doing so.
            //Files like something.php and something.php.jpg are definitely a problem and have to be restricted
            //If you need to make uploaded files accessed publicly, make sure they are really harmless.

            //create custom directory if not exist yet
            if (!is_dir(ipFile('file/secure/myCustomDir'))) {
                mkdir(ipFile('file/secure/myCustomDir'));
            }
            //Store uploaded files to our custom directory
            foreach($files as $key => $file) {
                //cleaning up original file name from any insecure symbols. Making sure we won't overwrite existing file
                $destinationFileName = ipUnoccupiedFileName(ipFile('file/secure/myCustomDir'), $originalFileNames[$key]);
                copy(ipFile('file/secure/tmp/' . $file), ipFile('file/secure/myCustomDir/' . $destinationFileName));
            }

        } else {
            $info = '<h3>You haven\'t uploaded any files</h3>';
        }





        $answer = array(
            'status' => 'success',
            'replaceHtml' => $info
        );

        return new \Ip\Response\Json($answer);

    }

}

# ChunkUploader - multiple, simultaneous uploads.

ChunkUploader field is a SilverStripe form field type, which can be used to upload large files faster.
Its built with [Resumable.js](http://www.resumablejs.com/) and supports multi file uploading.

### Requirements

1. SilverStripe 3.6.*
2. [Flow PHP Server](https://github.com/flowjs/flow-php-server)

### Usage

Install via composer.

```
composer require silverstripers/chunkuploader dev-master
```

### Using in forms

In the example below, we are creating a form with the chunk uploader.

```
public function Form()
{
    $form = new Form($this, 'Form', new FieldList(
        ChunkUploadField::create('Files')
            ->setRightTitle('Upload your files here.')
    ), new FieldList(
        FormAction::create('doUpload')
    ), new RequiredFields());
    return $form;
}

public function doUpload($data, $form)
{
    $files = $data['Files']
}
```

`$data['Files]'` returns a CSV list of file IDs.

![Form](http://i.cubeupload.com/6bhb2Z.png)

The above will make a form list this screenshot.

### Todo:

1. Use saved uploads with set value
2. Control number of files for uploading

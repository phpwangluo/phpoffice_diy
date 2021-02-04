<?php
/**
 * This file is part of PHPWord - A pure PHP library for reading and writing
 * word processing documents.
 *
 * PHPWord is free software distributed under the terms of the GNU Lesser
 * General Public License version 3 as published by the Free Software Foundation.
 *
 * For the full copyright and license information, please read the LICENSE
 * file that was distributed with this source code. For the full list of
 * contributors, visit https://github.com/PHPOffice/PHPWord/contributors.
 *
 * @see         https://github.com/PHPOffice/PHPWord
 * @copyright   2010-2018 PHPWord contributors
 * @license     http://www.gnu.org/licenses/lgpl.txt LGPL version 3
 */

namespace PhpOffice\PhpWord\Writer\HTML\Element;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\Element\Image as ImageElement;
use PhpOffice\PhpWord\Writer\HTML\Style\Image as ImageStyleWriter;
use App\Helpers\Tools;

/**
 * Image element HTML writer
 *
 * @since 0.10.0
 */
class Image extends Text
{
    /**
     * Write image
     *
     * @return string
     */
    public function write()
    {
        if (!$this->element instanceof ImageElement) {
            return '';
        }
        $content = '';
        $imageData = $this->element->getImageStringData(true);
        if ($imageData !== null) {
            $styleWriter = new ImageStyleWriter($this->element->getStyle());
            $style = $styleWriter->write();

            //$image_name = md5($this->element->getSource()) . '.' . $this->element->getImageExtension();
            $image_name = "_" . time() . "_" . Str::random(10).'.'.$this->element->getImageExtension();
            //$image_name = "_" . time() . "_" . Str::random(10).'.jpg';
            //$imageSrc = 'storage/app/public/upload/images/article_images/' .$image_name;
            $image_folder = request('image_folder');
            //$common_path = 'images/'.date("Y", time()).'/';
            $common_path = '';
            $file_path = 'public/upload/'.$image_folder.'/';
            //Storage::disk('local')->makeDirectory($file_path.$common_path);
            if(!Storage::exists($file_path.$common_path)){
                Storage::disk('local')->makeDirectory($file_path.$common_path);
            }
            $imageSrc = 'storage/upload/'.$image_folder.'/'.$common_path.$image_name;

            // 这里可以自己处理，上传oss之类的
            file_put_contents($imageSrc, base64_decode($imageData));
            $save_page = 'storage/upload/'.$image_folder.'/'.$common_path."_" . time() . "_" . Str::random(10).'.jpg';
            $changeImageType = Tools::transform_image($imageSrc,'jpeg',$save_page);
            Storage::delete($file_path.$common_path.$image_name);
            //$imageData = 'data:' . $this->element->getImageType() . ';base64,' . $imageData;

            $content .= $this->writeOpening();
            $content .= "<img  border=\"0\"  style=\"{$style}\" src=\"/{$save_page}\"/>";
            //$content .= "<img border=\"0\" style=\"{$style}\" src=\"{$imageData}\"/>";
            $content .= $this->writeClosing();
        }

        return $content;
    }
    public function mkdirs($dir, $mode = 0777)
    {
        if (is_dir($dir) || @mkdir($dir, $mode)) return TRUE;
        if (!$this->mkdirs(dirname($dir), $mode)) return FALSE;
        return @mkdir($dir, $mode);
    }
}

# WMS

WMS project for Natue, find more info [here](https://natuelabs.atlassian.net/wiki/display/WMS/WMS).

## How to install

- Create a database
- Install node dependencies: `npm install`
- Install php dependencies: `composer install`
- Build: `bin/robo build`

## Third party requirements

- Install ```wkhtmltopdf``` (version 0.9.9)

The standard installation of wkhtmltopdf, requires an xserver.
You should download a precompiled version.

```bash
wget http://wkhtmltopdf.googlecode.com/files/wkhtmltopdf-0.9.9-static-amd64.tar.bz2
tar xvjf wkhtmltopdf-0.9.9-static-amd64.tar.bz2
sudo mv wkhtmltopdf-amd64 /usr/bin/wkhtmltopdf
sudo chmod +x /usr/bin/wkhtmltopdf
```

- Install ```barcode```:
  * BarcodeBundle needs an external programm for encodings other then EAN/ISBN
  * download gnu-barcode from www.gnu.org/software/barcode/
  * compile and install them
  * download genbarcode from www.ashberg.de/php-barcode/
  * compile and install them
  * specify path to genbarcode in php-barcode.php
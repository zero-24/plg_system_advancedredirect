#!/bin/bash
EXTENSION_ZIP_FILENAME="build/plg_system_advancedredirect.zip"
EXTENSION_ELEMENT="advancedredirect"
if [ ! -f "$EXTENSION_ELEMENT.xml" ]; then cd ..; fi
if [ -f "$EXTENSION_ZIP_FILENAME" ]; then rm $EXTENSION_ZIP_FILENAME; fi
zip -r $EXTENSION_ZIP_FILENAME language/ subform/ "$EXTENSION_ELEMENT.php" "$EXTENSION_ELEMENT.xml" script.php --quiet
SHA512=$(sha512sum $EXTENSION_ZIP_FILENAME | awk '{print $1}')
sed -i -e "s/\(<sha512>\).*\(<\/sha512>\)/<sha512>$SHA512<\/sha512>/g" update.xml
echo 'package and update server ready'

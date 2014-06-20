<?php /*

[ImageMagick]
Filters[]=thumb=-resize 'x%1' -resize '%1x<' -resize 50%
Filters[]=centerimg=-gravity center -crop %1x%2+0+0 +repage
Filters[]=strip=-strip
Filters[]=sharpen=-sharpen 0.5

[AliasSettings]
AliasList[]=header_banner
AliasList[]=header_logo
AliasList[]=icon

[header_banner]
Reference=reference
Filters[]=geometry/scalewidthdownonly=1000
Filters[]=centerimg=1000;200

[header_logo]
Reference=reference
Filters[]=geometry/scalewidthdownonly=1000

[icon]
Reference=reference
Filters[]=geometry/scaleheight=14

*/ ?>

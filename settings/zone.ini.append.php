<?php /*

[General]
#Default eZFlow
AllowedTypes[]
AllowedTypes[]=GlobalZoneLayout
AllowedTypes[]=2ZonesLayout1
AllowedTypes[]=2ZonesLayout2
AllowedTypes[]=3ZonesLayout1
AllowedTypes[]=3ZonesLayout2
AllowedTypes[]=4ZonesLayout1

#Default OpenPA
AllowedTypes[]=3ZonesLayout3
AllowedTypes[]=4ZonesLayout2
AllowedTypes[]=5ZonesLayout1
AllowedTypes[]=5ZonesLayout2
AllowedTypes[]=6ZonesLayout1
AllowedTypes[]=7ZonesLayout1
AllowedTypes[]=1ZonesLayoutFolder
AllowedTypes[]=0ZonesLayoutFolder

[GlobalZoneLayout]
ZoneTypeName=Layout globale
Zones[]
Zones[]=main
ZoneName[]
ZoneName[main]=Zona unica
ZoneThumbnail=globalzone_layout.gif
Template=globalzonelayout.tpl
AvailableForClasses[]=global_layout
AvailableForClasses[]=area


[2ZonesLayout1]
Zones[]
ZoneName[]
ZoneTypeName=2 zone (tipo 1)
Zones[]=left
Zones[]=right
ZoneName[left]=Colonna sinistra
ZoneName[right]=Colonna destra
ZoneThumbnail=2zones_layout1.gif
Template=2zoneslayout1.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[2ZonesLayout2]
Zones[]
ZoneName[]
ZoneTypeName=2 zone (tipo 2)
Zones[]=left
Zones[]=right
ZoneName[left]=Colonna sinistra
ZoneName[right]=Colonna destra
ZoneThumbnail=2zones_layout2.gif
Template=2zoneslayout2.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[3ZonesLayout1]
Zones[]
ZoneName[]
ZoneTypeName=3 zone (tipo 1)
Zones[]=left
Zones[]=right
Zones[]=bottom
ZoneName[left]=Colonna sinistra
ZoneName[right]=Colonna destra
ZoneName[bottom]=Zona inferiore
ZoneThumbnail=3zones_layout1.gif
Template=3zoneslayout1.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[3ZonesLayout2]
Zones[]
ZoneName[]
ZoneTypeName=3 zone (tipo 2)
Zones[]=left
Zones[]=right
Zones[]=bottom
ZoneName[left]=Colonna sinistra
ZoneName[right]=Colonna destra
ZoneName[bottom]=Zona inferiore
ZoneThumbnail=3zones_layout2.gif
Template=3zoneslayout2.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[4ZonesLayout1]
Zones[]
ZoneName[]
ZoneTypeName=4 zone (tipo 1)
Zones[]=left
Zones[]=right
Zones[]=bottomleft
Zones[]=bottomright
ZoneName[left]=Colonna sinistra
ZoneName[right]=Colonna destra
ZoneName[bottomleft]=Zona inferiore sinistra
ZoneName[bottomright]=Zona inferiore destra
ZoneThumbnail=4zones_layout1.gif
Template=4zoneslayout1.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[3ZonesLayout3]
Zones[]
ZoneName[]
ZoneTypeName=3 zone (tipo 3)
Zones[]=left
Zones[]=center
Zones[]=right
ZoneName[left]=Colonna sinistra
ZoneName[center]=Colonna centrale
ZoneName[right]=Colonna destra
ZoneThumbnail=3zoneslayout3.gif
Template=3zoneslayout3.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[4ZonesLayout2]
Zones[]
ZoneName[]
ZoneTypeName=4 zone (tipo 2)
Zones[]=left
Zones[]=right
Zones[]=bottomleft
Zones[]=bottomright
ZoneName[left]=Zona superiore sinistra
ZoneName[right]=Zona superiore destra
ZoneName[bottomleft]=Zona inferiore
ZoneName[bottomright]=Colonna destra
ZoneThumbnail=4zoneslayout2.gif
Template=4zoneslayout2.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[5ZonesLayout1]
Zones[]
ZoneName[]
ZoneTypeName=5 zone (tipo 1)
Zones[]=top
Zones[]=central_left
Zones[]=centra_middle
Zones[]=left
Zones[]=bottom
ZoneName[top]=Zona superiore
ZoneName[central_left]=Zona centrale a sinistra
ZoneName[centra_middle]=Zona centrale a destra
ZoneName[left]=Colonna destra
ZoneName[bottom]=Zona inferiore
ZoneThumbnail=5zoneslayout1.jpg
Template=5zoneslayout1.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino

[5ZonesLayout2]
Zones[]
ZoneName[]
ZoneTypeName=5 zone (tipo 2)
Zones[]=top
Zones[]=central_left
Zones[]=centra_middle
Zones[]=left
Zones[]=bottom
ZoneName[top]=Zona superiore
ZoneName[central_left]=Zona centrale a sinistra
ZoneName[centra_middle]=Zona centrale a destra
ZoneName[left]=Colonna destra
ZoneName[bottom]=Zona inferiore
ZoneThumbnail=5zoneslayout2.jpg
Template=5zoneslayout2.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite
AvailableForClasses[]=albotelematicotrentino


#usato nel folder
[1ZonesLayoutFolder]
Zones[]
ZoneName[]
ZoneTypeName=Colonna destra del Folder
Zones[]=right
ZoneName[right]=Colonna destra
ZoneThumbnail=colonnadestrafolder.gif
Template=colonnadestrafolder.tpl
AvailableForClasses[]=folder
AvailableForClasses[]=pagina_sito

[2ZonesLayoutFolder]
Zones[]
ZoneName[]
ZoneTypeName=Due colonne del Folder
Zones[]=left
Zones[]=right
ZoneName[left]=Colonna sinistra
ZoneName[right]=Colonna destra
ZoneThumbnail=duecolonnefolder.gif
Template=duecolonnefolder.tpl
AvailableForClasses[]=folder
AvailableForClasses[]=pagina_sito

[0ZonesLayoutFolder]
Zones[]
ZoneName[]
ZoneTypeName=Nessuna colonna nel Folder
Zones[]=nessuna
ZoneName[nessuna]=Gestione automatica dei contenuti
ZoneThumbnail=nessuna.gif
Template=emptyfolder.tpl
AvailableForClasses[]=folder
AvailableForClasses[]=pagina_sito

[6ZonesLayout1]
Zones[]
ZoneName[]
ZoneTypeName=6 zone (tipo 1)
Zones[]=top
Zones[]=central_left
Zones[]=centra_middle
Zones[]=left
Zones[]=bottom
Zones[]=bottom_left
ZoneName[top]=Colonna sinistra in alto
ZoneName[central_left]=Al centro a sinistra
ZoneName[centra_middle]=Al centro a destra
ZoneName[left]=Colonna destra
ZoneName[bottom]=Colonna sinistra in basso
ZoneName[bottom_left]=Zona inferiore
ZoneThumbnail=6zoneslayout1.jpg
Template=6zoneslayout1.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite

[7ZonesLayout1]
Zones[]
ZoneName[]
ZoneTypeName=7 zone (tipo 1)
Zones[]=top
Zones[]=central_left
Zones[]=centra_middle
Zones[]=left
Zones[]=bottom
Zones[]=bottom_left
Zones[]=bottom_right
ZoneName[top]=Colonna sinistra in alto
ZoneName[central_left]=Al centro a sinistra
ZoneName[centra_middle]=Al centro a destra
ZoneName[left]=Colonna destra
ZoneName[bottom]=Colonna sinistra in basso
ZoneName[bottom_left]=Zona inferiore sinistra
ZoneName[bottom_right]=Zona inferiore destra
ZoneThumbnail=7zoneslayout1.jpg
Template=7zoneslayout1.tpl
AvailableForClasses[]=frontpage
AvailableForClasses[]=area_tematica
AvailableForClasses[]=progetto
AvailableForClasses[]=homepage
AvailableForClasses[]=homepage_interna
AvailableForClasses[]=subsite



*/ ?>

<?php /*

# Per impedire l'indicizzazione dei file scaricati con content/download attivare la seguente regola
#[Event]
#Listeners[]=content/download@OpenPADownloadFilter::addXRobotsTagHeader


[Cache]
CacheItems[]=openpamenu
CacheItems[]=openpadynamicini
CacheItems[]=openpacontextdata
CacheItems[]=openpaorganigramma

[Cache_openpamenu]
name=OpenPA Menu cache
id=openpamenu
tags[]=openpa
path=openpa/menu
isClustered=true

[Cache_openpadynamicini]
name=OpenPA Configurazioni di visualizzazione
id=openpaini
tags[]=openpa
path=openpa/ini
class=OpenPAINI
isClustered=true

[Cache_openpacontextdata]
name=OpenPA Pagedata cache
id=openpacontextdata
tags[]=openpa
class=OpenPAPageData
path=openpa/pagedata
isClustered=true

[Cache_openpaorganigramma]
name=OpenPA Organigramma
id=openpaorganigramma
tags[]=openpa
class=OpenPAOrganigrammaTools
path=openpa/organigramma
isClustered=true

[RoleSettings]
PolicyOmitList[]=openpa/classdefinition
PolicyOmitList[]=openpa/calendar
PolicyOmitList[]=openpa/object
PolicyOmitList[]=openpa/data
PolicyOmitList[]=openpa/signup
PolicyOmitList[]=openpa/cookie
PolicyOmitList[]=robots.txt
PolicyOmitList[]=openpa/loadwt
PolicyOmitList[]=openpa/changestatedefinition
PolicyOmitList[]=openpa/changesectiondefinition
PolicyOmitList[]=sitemap.xml

[RegionalSettings]
TranslationExtensions[]=openpa

[TemplateSettings]
ExtensionAutoloadPath[]=openpa

*/ ?>

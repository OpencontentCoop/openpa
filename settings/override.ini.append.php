<?php /* #?ini charset="utf-8"?

#####################################################
##################### FULL ##########################
#####################################################

[full_apps_container]
Source=node/view/full.tpl
MatchFile=full/apps_container.tpl
Subdir=templates
Match[class_identifier]=apps_container

#####################################################
##################### DATATYPE ######################
#####################################################


[view_materia_associazione]
Source=content/datatype/view/ezobjectrelationlist.tpl
MatchFile=datatype/materia_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=associazione
Match[attribute_identifier]=materia

[edit_materia_associazione]
Source=content/datatype/edit/ezobjectrelationlist.tpl
MatchFile=edit/datatype/materia_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=associazione
Match[attribute_identifier]=materia

[edit_organo_politico_membri]
Source=content/datatype/edit/ezobjectrelationlist.tpl
MatchFile=edit/datatype/organo_politico_membri_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=organo_politico
Match[attribute_identifier]=membri

[view_materia_new]
Source=content/datatype/view/ezobjectrelationlist.tpl
MatchFile=datatype/materia_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=event_new
Match[attribute_identifier]=materia

[view_materia]
Source=content/datatype/view/ezobjectrelationlist.tpl
MatchFile=datatype/materia_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=event
Match[attribute_identifier]=materia

[edit_materia_new]
Source=content/datatype/edit/ezobjectrelationlist.tpl
MatchFile=edit/datatype/materia_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=event_new
Match[attribute_identifier]=materia

[edit_materia]
Source=content/datatype/edit/ezobjectrelationlist.tpl
MatchFile=edit/datatype/materia_ezobjectrelationlist.tpl
Subdir=templates
Match[class_identifier]=event
Match[attribute_identifier]=materia

[view_timetable]
Source=content/datatype/view/ezmatrix.tpl
MatchFile=datatype/timetable_ezmatrix.tpl
Subdir=templates
Match[attribute_identifier]=timetable

[edit_contacts]
Source=content/datatype/edit/ezmatrix.tpl
MatchFile=edit/datatype/contacts_ezmatrix.tpl
Subdir=templates
Match[attribute_identifier]=contacts

#####################################################
###################### BLOCCHI ######################
#####################################################

[block_accesso_area_riservata]
Source=block/view/view.tpl
MatchFile=block/accesso_area_riservata.tpl
Subdir=templates
Match[type]=AreaRiservata
Match[view]=accesso_area_riservata

[block_class_filter]
Source=block/view/view.tpl
MatchFile=block/class_filter.tpl
Subdir=templates
Match[type]=ContentSearch
Match[view]=class_filter

[block_search_class]
Source=block/view/view.tpl
MatchFile=block/search_class.tpl
Subdir=templates
Match[type]=ContentSearch
Match[view]=search_class

[block_search_class_and_attributes]
Source=block/view/view.tpl
MatchFile=block/search_class_and_attributes.tpl
Subdir=templates
Match[type]=ContentSearch
Match[view]=search_class_and_attributes

[block_search_free_ajax]
Source=block/view/view.tpl
MatchFile=block/search_free_ajax.tpl
Subdir=templates
Match[type]=ContentSearch
Match[view]=search_free_ajax

[block_video_ez]
Source=block/view/view.tpl
MatchFile=block/videoez.tpl
Subdir=templates
Match[type]=VideoPlayer
Match[view]=video_ez

[block_video_flow_playlist]
Source=block/view/view.tpl
MatchFile=block/videoflow_playlist.tpl
Subdir=templates
Match[type]=VideoPlayer
Match[view]=video_flow_playlist

[block_video_flow]
Source=block/view/view.tpl
MatchFile=block/videoflow.tpl
Subdir=templates
Match[type]=VideoPlayer
Match[view]=video_flow

[block_feed_reader]
Source=block/view/view.tpl
MatchFile=block/feed_reader.tpl
Subdir=templates
Match[type]=FeedRSS
Match[view]=feed_reader

#[block_feed_meteo]
#Source=block/view/view.tpl
#MatchFile=block/feed_meteo.tpl
#Subdir=templates
#Match[type]=FeedRSS
#Match[view]=feed_meteo

[block_singolo_img]
Source=block/view/view.tpl
MatchFile=block/singolo_img.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_img

[block_singolo_img_interne]
Source=block/view/view.tpl
MatchFile=block/singolo_img_interne.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_img_interne

[block_singolo_img_interne_piccolo]
Source=block/view/view.tpl
MatchFile=block/singolo_img_interne_piccolo.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_img_interne_piccolo

[block_singolo_imgtit_interne]
Source=block/view/view.tpl
MatchFile=block/singolo_imgtit_interne.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_imgtit_interne

[block_singolo_imgtit_interne_piccolo]
Source=block/view/view.tpl
MatchFile=block/singolo_imgtit_interne_piccolo.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_imgtit_interne_piccolo

[block_singolo_imgtit]
Source=block/view/view.tpl
MatchFile=block/singolo_imgtit.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_imgtit

[block_singolo_box]
Source=block/view/view.tpl
MatchFile=block/singolo_box.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_box

[block_singolo_box_piccolo]
Source=block/view/view.tpl
MatchFile=block/singolo_box_piccolo.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_box_piccolo

[block_singolo_banner]
Source=block/view/view.tpl
MatchFile=block/singolo_banner.tpl
Subdir=templates
Match[type]=Singolo
Match[view]=singolo_banner

[block_lista_num]
Source=block/view/view.tpl
MatchFile=block/lista_num.tpl
Subdir=templates
Match[type]=Lista
Match[view]=lista_num

[block_lista_accordion]
Source=block/view/view.tpl
MatchFile=block/lista_accordion.tpl
Subdir=templates
Match[type]=Lista
Match[view]=lista_accordion

[block_lista_accordion_3]
Source=block/view/view.tpl
MatchFile=block/lista_accordion_manual.tpl
Subdir=templates
Match[type]=Lista3
Match[view]=lista_accordion_manual

[block_lista_box]
Source=block/view/view.tpl
MatchFile=block/lista_box.tpl
Subdir=templates
Match[type]=Lista
Match[view]=lista_box

[block_lista_box_3]
Source=block/view/view.tpl
MatchFile=block/lista_box2.tpl
Subdir=templates
Match[type]=Lista3
Match[view]=lista_box2

[block_lista_box_4]
Source=block/view/view.tpl
MatchFile=block/lista_box3.tpl
Subdir=templates
Match[type]=Lista3
Match[view]=lista_box3

[block_lista_tab]
Source=block/view/view.tpl
MatchFile=block/lista_tab.tpl
Subdir=templates
Match[type]=Lista
Match[view]=lista_tab

[block_lista_tab_3]
Source=block/view/view.tpl
MatchFile=block/lista_tab.tpl
Subdir=templates
Match[type]=Lista3
Match[view]=lista_tab

[block_lista_carousel]
Source=block/view/view.tpl
MatchFile=block/lista_carousel.tpl
Subdir=templates
Match[type]=Lista
Match[view]=lista_carousel

[block_eventi]
Source=block/view/view.tpl
MatchFile=block/eventi.tpl
Subdir=templates
Match[type]=Eventi
Match[view]=eventi

[block_eventi_manual]
Source=block/view/view.tpl
MatchFile=block/eventi_manual.tpl
Subdir=templates
Match[type]=Eventi
Match[view]=eventi_manual

[block_iosono]
Source=block/view/view.tpl
MatchFile=block/iosono.tpl
Subdir=templates
Match[type]=Iosono
Match[view]=iosono

*/ ?>

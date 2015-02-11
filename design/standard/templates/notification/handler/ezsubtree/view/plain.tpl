{def $siteurl = ezini("SiteSettings","SiteURL") $sitename = ezini("SiteSettings","SiteName")}
{if sensor_classes|contains( $object.class_identifier )}{set $siteurl = sensor_site_url()}{/if}
{let is_update=false()}
{section loop=$object.versions}{if and($:item.status|eq(3),$:item.version|ne($object.current_version))}{set is_update=true()}{/if}{/section}
{section show=$is_update}
{set-block scope=root variable=subject}{$object.content_class.name|wash} [{$sitename} - {$object.main_node.parent.name|wash}]{/set-block}
{set-block scope=root variable=from}{concat($object.current.creator.name|wash,' <', $sender, '>')}{/set-block}
{set-block scope=root variable=message_id}{concat('<node.',$object.main_node_id,'.eznotification','@',$siteurl,'>')}{/set-block}
{set-block scope=root variable=reply_to}{concat('<node.',$object.main_node_id,'.eznotification','@',$siteurl,'>')}{/set-block}
{set-block scope=root variable=references}{section name=Parent loop=$object.main_node.path_array}{concat('<node.',$:item,'.eznotification','@',$siteurl,'>')}{delimiter}{" "}{/delimiter}{/section}{/set-block}
{"This email is to inform you that an updated item has been published at %sitename."|i18n('design/standard/notification','',hash('%sitename',$sitename))}
{"The item can be viewed by using the URL below."|i18n('design/standard/notification')}

{$object.name|wash} - {$object.current.creator.name|wash} (Owner: {$object.owner.name|wash})
{section-else}
{set-block scope=root variable=subject}{$object.content_class.name|wash} [{$sitename} - {$object.main_node.parent.name|wash}]{/set-block}
{set-block scope=root variable=from}{concat($object.owner.name,' <', $sender, '>')}{/set-block}
{set-block scope=root variable=message_id}{concat('<node.',$object.main_node_id,'.eznotification','@',$siteurl,'>')}{/set-block}
{set-block scope=root variable=reply_to}{concat('<node.',$object.main_node.parent_node_id,'.eznotification','@',$siteurl,'>')}{/set-block}
{set-block scope=root variable=references}{section name=Parent loop=$object.main_node.parent.path_array}{concat('<node.',$:item,'.eznotification','@',$siteurl,'>')}{delimiter}{" "}{/delimiter}{/section}{/set-block}
{"This email is to inform you that a new item has been published at %sitename."|i18n('design/standard/notification','',hash('%sitename',$sitename))}
{"The item can be viewed by using the URL below."|i18n('design/standard/notification')}

{$object.class_name} - {$object.owner.name|wash}
{/section}

http://{$siteurl}{$object.main_node.url_alias|ezurl(no)}

{"If you do not want to continue receiving these notifications,
change your settings at:"|i18n('design/standard/notification')}
http://{$siteurl}{concat("notification/settings/")|ezurl(no)}

-- 
{"%sitename notification system"
 |i18n('design/standard/notification',,
       hash('%sitename',$siteurl))}
{/let}

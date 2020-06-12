=== WooCommerce Vendors and Customers Conversation ===
Contributors: nmedia
Tags: woocommerce, vendor customer messages, vendor message, client messages, private message, woocommerce private message, woocommerce file upload, woocommerce order complete, woocommerce order message
Donate link: https://najeebmedia.com/donate/
Requires at least: 3.5
Tested up to: 5.2
Stable tag: 6.5
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

WooCommerce Vendors and Customers Conversation

== Description ==
WooCommerce only plugin allow vendros and customers to send messages after order is placed. Each order has it's own conversation
panel. See Quick Video Below

[vimeo https://vimeo.com/288485898]

= PRO Features =
* File Attachments
* Images Attachments
* Email Conversation
* Filetype, size control
* Images Thumbs
* Dedicated Support Forum
* [Get PRO](https://najeebmedia.com/wordpress-plugin/woocommerce-file-upload-plugin-after-checkout/)

= Demo =
[Click Here](https://filemanager.nmediahosting.com/wooconvo-vendor-and-customer-messages/)

= Hooks/Filters for Developers =
Following filters can be used to personalized/overrirde plugin design.

<pre>add_filter(wooconvo_message_receivers, $receivers)</pre>
$receivers: array of receivers
Email notification receivers
<pre>add_filter(wooconvo_message_subject, $subject, $order_id)</pre>
Subject of Email notification sent to both users.
<pre>add_filter(wooconvo_shop_admin_name, $vendor_name, $order_id)</pre>
Title for admin shown for Admin to user
<pre>add_filter(wooconvo_render_attachments, $html, $files)</pre>
Render attachments sent with message. This is a pro feature

= Aso Supported for Multi Vendor Plugins =
Compatible with following two most advanced Multi Vendor Plugins for WooCommerce
* [WooCommerce Product Vendors](https://woocommerce.com/products/product-vendors/)
* [WooCommerce Marketplace (WCMp)](https://wc-marketplace.com/)

Each Vendor can see messages in his order panel and send reply to customers

== Installation ==
1. Upload plugin directory to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. After activation, you can set options from `WooCommerce -> WooConvo` menu

== Frequently Asked Questions ==
= Where admin will see messages? =
Admin can see messages in each order inside a Meta Box

= Can user or admin files/images with messages? =
Yes, but it's a PRO feature

== Screenshots ==
1. My Account Messages Button
2. Frontend Messages UI
3. Admin Messages UI
4. Admin Settings
5. WooConvo in WooCommerce Orders Columns

== Changelog ==
= 6.5 June 2, 2020 =
* Feature: [Compatible with WooCommerce Sequential Order Numbers](https://clients.najeebmedia.com/forums/topic/email-title-problem/#post-17234)
= 6.4 May 17, 2020 =
* Feature: [Wooconvo Revision Addon](https://najeebmedia.com/wooconvo/)
* Feature: File filed required
* Bug Fixed: WCMP suborder design
* Bug Fixed: WCMP suborder design
= 6.3 May 05, 2020 =
* Bug Fixed: get_wcmp_suborders undefine.
= 6.2 April 30, 2020 =
* Feature: Once the files sent then automatically files are remove from select area. no longer attach with other msg.
* Feature: Setting user can send the image or files links in the email.
* Feature: Only files can be sent in chatbox without text.
* Feature: Chat box hide on parent order.
* Feature: Change the files design.
* Feature: Compatible with WCMP plugin.
= 6.1 Jan 27, 2020 =
* Feature: [New notification handle with WCMP](https://wordpress.org/support/topic/pre-purchase-questions-37/)
* Bug fixed : Remove the footer-text form email template.
= 6.0 Dec 17, 2019 =
* Bug fixed: Settings page responsive on mobile
* Bug fixed: Messages area render on user order page
= 5.9 Dec 16, 2019 =
* Bug fixed: Screen options disabled issue fixed
= 5.8 Dec 7, 2019 = 
* Feature: [New message notification in orders manager ](https://ppom.nmdevteam.com/wp-content/uploads/2019/12/Capture.png)
* Feature: Change the setting UI
= 5.7 Sep 9, 2019 = 
* Feature:  WCMp latest version cmopaitbility added
* Bug fixed: WC email template integration
* Bug fixed: Email notification vendor to admin.
= 5.6 Aug 27, 2019 =
* Feature: Notification email connected with WC Emails Settings.
= 5.5 May 20, 2019 =
* Feature: [Now text message can be added inside Notification Email](https://clients.najeebmedia.com/forums/topic/message-sent-successfully-email-notification-couldnt-be-sent/)
= 5.4 March 2, 2019 =
* Bug fixed: Attachments download issue fixed
= 5.3 December 15, 2018 =
* Bug fixed: Vendor Display Name fixed in message when used with WCMP
* Bug fixed: Extra spaces removed from end of all files.
= 5.2 December 10, 2018 =
* Bug fixed: CSS issue fixed for large message being hidden under Send button
* Tweak: WooCommerce 3.5 and WordPress 5.0 Check
= 5.1 November 15, 2018 =
* Bug fixed: Order URL wrong for when used with WCMp (WooCommerce Marketplace) plugin
= 5.0 October 16, 2018 =
* Features: Language translation support added
= 4.3 October 10, 2018 =
* Bug fixed: Order URL issue fixed with WCMP
= 4.2 September 26, 2018 =
* Bug fixed: [Safar/iPohne issue fixed for file uploader](https://clients.najeebmedia.com/forums/topic/upload-button-not-working-on-mobile-on-view-order-page/#post-9427)
* Bug fixed: Avatar were not working, it's fixed
* Features: Plupload loaded from WP Core
* Features: Code optimized for fast response, extra code removed
* Features: Control location for conversation box on order page (after/before order details)
* Features: UI Block added while uploading file
= 4.1 September 11, 2018 =
* Features: Column added in WooCommerce Orders list to quickly access conversation [screenshot-5.jpg]
= 4.0 September 6, 2018 =
* Bug fixed: Deprecated functions removed
* Bug fixed: Some warnings removed
* Features: Supported with [WooCommerce Product Vendors](https://woocommerce.com/products/product-vendors/)
* Features: Supported with [WooCommerce Marketplace (WCMp)](https://wc-marketplace.com/)

= 1.1 May 15, 2017 =
* Bug fixed: Plupload script removed
* Bug fixed: some function renamed
* Bug fixed: Sanitized all input field
* Bug fixed: use current_user_can() function for ajax requests
* Bug fixed: Removed unnecessary functions
* Bug fixed: EasyTabs replaced with WordPress core jquery-ui script

= 1.0 March 20, 2017 =
* Initial Release

== Upgrade Notice ==
= !! Love For All Hatred For None !! =
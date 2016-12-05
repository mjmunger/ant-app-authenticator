# ant-app-authenticator
Handles basic authentication for PHP-Ant. The installation of this app assumes that all content is protected by default, and that only certain "allowed" public URLs are now going to be visible.

# Quick Setup
1. Install this app in your apps directory for PHP Ant (DOCUMENT_ROOT/includes/apps/)
2. Enable the app in the cli
3. Create your whitelist for URLs that do not require authentication. At a minimum, you will need to whitelist /login/ or else you'll get stuck in a redirect loop!
4. Install a login app to handle logins.

    Ant*CLI> authentication uri whitelist add /login/

# Adding whitelisted URLs.

In the CLI, you can add / remove URIs that are whitelisted (allowed public access without authentcation) using the command:

    Ant*CLI> authentication uri whitelist [add | remove] Regex

To add /faq/ to the whitelist, for example, use:

    Ant*CLI> authentication uri whitelist add /faq/

If you have a wizard that has 5 steps, and moves you from /wizard/1 to /wizard/2 to /wizard/3, and so on, you would create the following regex:

    Ant*CLI> authentication uri whitelist add /wizard/[1-5]/

# Removing whitelisted URLs

There are two ways to remove whitelisted URLs. You can use the remove command as the inverse of the add command like so:

    Ant*CLI> authentication uri whitelist remove /wizard/[1-5]/

Or, you can use the index of the rule you want to remove by first looking at rules using the show command:


    Ant*CLI> authentication uri whitelist show
    
    -----------------------
    num   URI Regex        
    -----------------------
    0     /login/          
    1     /login/          
    2     /wizard/[1-5]/   

and then using the *num* value as the removal argument:

    Ant*CLI> authentication uri whitelist remove 2
    Removing entry 2 (/wizard/[1-5]/) from URI Whitelist Registry
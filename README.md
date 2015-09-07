A PHP Wrapper for Teamwork Desk's API (That Is Completely Undocumented)
=======================================================================

I wrote this completely out of necessity. As of writing this there are no other
PHP wrappers for this API, Zapier doesn't support it, and no one that I can find
is talking about it. All of these are probably because there is 0 documentation.
We wanted to switch to Teamwork Desk from Zendesk because of the great
integrations with Teamwork PM, and some of the useful features Teamwork Desk
offers are not available in Zendesk.

As for the API, I found some API-looking calls when I watch the network usage in
Teamwork Desk, and I assumed the authentication would be the same as Teamwork
PM. After some testing, I found that the API exists, and is complete as far as I
can tell; there's just no documentation. Since we're mostly "wingin' it" at this
point, feel free to suggest changes and additions.

Getting Started:
----------------

Define two constants, TEAMWORK_DESK_DOMAIN and TEAMWORK_DESK_KEY with your
Teamwork Desk domain and your API key.

    <?php
    define('TEAMWORK_DESK_DOMAIN',   'something.teamwork.com');
    define('TEAMWORK_DESK_KEY',      'xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx');

Next, there are some classes you can use:

    $alert          = new Teamwork\Desk\Alert();
    $customer       = new Teamwork\Desk\Customer();
    $inbox          = new Teamwork\Desk\Inbox();
    $plan           = new Teamwork\Desk\Plan();
    $setting        = new Teamwork\Desk\Setting();
    $ticket         = new Teamwork\Desk\Ticket();
    $ticketPriority = new Teamwork\Desk\TicketPriority();
    $ticketSource   = new Teamwork\Desk\TicketSource();
    $ticketStatus   = new Teamwork\Desk\TicketStatus();
    $ticketType     = new Teamwork\Desk\TicketType();
    $user           = new Teamwork\Desk\User();

From there, there are some common methods:

    $ticket->getAll(['sortBy' => 'id']);
    $ticket->get(12345);
    $user->getByName('Stuart');

Also, you can access properties of the objects like this:

    echo $ticket->subject;
    $ticketNumber = $ticket->id;

You can create an object like this:

    $inbox  = new Teamwork\Desk\Inbox();
    $ticket = new Teamwork\Desk\Ticket();
    $ticket->customerEmail = 'user@example.com';
    $ticket->subject       = 'Server on fire!';
    $ticket->message       = 'The server is actually on fire!';
    $ticket->priority      = 'High';
    $ticket->inboxId       = $inbox->getByName('Server Stuff');
    $ticket->create();


## Version 1.0.3 (2026101501)
* Improvement: The Wizard is on after installation. Sites that installed 1.0.1 or 1.0.2 are switched on by the upgrade, unless an admin has changed "Enable Wizard" before (here or in the bundled booking wizard). (Wunderbyte-GmbH/Wunderbyte-GmbH#2605)
* Improvement: The Wizard is no longer marked as experimental. (Wunderbyte-GmbH/Wunderbyte-GmbH#2605)
* Improvement: The activation hint and the update notice describe what the Wizard does in Moodle - finding content and people, courses and activities, quizzes and questions, reports, access, enrolment and notification problems - instead of booking options and the booking activity. (Wunderbyte-GmbH/Wunderbyte-GmbH#2605)

## Version 1.0.2 (2026101500)
* Bugfix: The privacy mode fails closed: when it is unset, empty or unknown, the Wizard masks personal data strictly, as the setting default and the trial consent dialog promise. Only an explicit "off" or "soft" weakens the masking. Before, an unset mode sent personal data unmasked. (Wunderbyte-GmbH/Wunderbyte-GmbH#2601)
* Bugfix: Creating or updating a URL or a Page sends the link and the page text as their own fields (externalurl, content) instead of a nested settings object, so the model writes valid requests for links and page texts; listing course categories and reports always sets a limit, so a request is never empty. New activities always get a name, except for a label. (Wunderbyte-GmbH/Wunderbyte-GmbH#2600)
* Improvement: The course skills say when to choose them: creating a new, empty course, filling a named course or making it complete, and looking at what a course contains and hides. Creating a course fences off adding to an existing course and the self-paced booking option; the course analysis fences off the details of a booking option. A request about a course's contents or a named course to complete now reaches the right skill. (Wunderbyte-GmbH/Wunderbyte-GmbH#2589, Wunderbyte-GmbH/Wunderbyte-GmbH#2595)
* Improvement: Shorter field descriptions for several skills (permissions, course enrolment, user in course, documentation, skills list, remember, add activity), each a complete statement within what the model reads of a field. (Wunderbyte-GmbH/Wunderbyte-GmbH#2582)
* Documentation: The user guide, the docs landing page, the README and the skills catalog describe the Wizard as it ships: confirmation exceptions, default activation of skills, the settings page, the wand setting and default roles, the privacy-mode fallback, every skill with its risk class and inputs; the calendar and messages page is removed. (Wunderbyte-GmbH/Wunderbyte-GmbH#2601)

## Version 1.0.1 (2026101400)
* Improvement: The Wizard is off by default on new installations; an admin switches it on with "Enable Wizard". Sites that already have the setting stored keep it. (Wunderbyte-GmbH/Wunderbyte-GmbH#2605)
* Bugfix: Unticking "Enable Wizard" switches the whole Wizard off - the navbar wand, the chat, the shortcode and all its webservices. Before, the setting was only read on the settings page. (Wunderbyte-GmbH/Wunderbyte-GmbH#2605)
* Improvement: The plugin is just the "Wizard": no string names "Booking Wizard" or "Booking extension" any more. (Wunderbyte-GmbH/Wunderbyte-GmbH#2605)
* Bugfix: An existing Wunderbyte access configures the Wunderbyte AI provider in one step - no second trial; the clone is named Wunderbyte, the old instance is switched off and never duplicated.
* Bugfix: A trial on the core OpenAI provider also routes summarise_text to the trial gateway, and on Moodle 4.5 an empty system instruction replaces the core default.
* Documentation: README for the standalone plugin and a docs landing page; the booking skills are marked as available with mod_booking only. (Wunderbyte-GmbH/Wunderbyte-GmbH#2584)

## Version 1.0.0 (2026100601)
* New feature: First release of the standalone Wizard (local_wizard): the AI assistant of Wunderbyte's booking wizard as its own plugin, installable without mod_booking. Users describe in their own words what they need, the Wizard picks the matching skill, asks only for what is missing and shows a confirmation before anything is changed.

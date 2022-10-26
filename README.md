# Gamification

A Moodle local plugin to provide gamification features at platform level.

## Important note

This plugin is a work in progress. It was created as a proof of concept for a MoodleMoot Global 2022 presentation (check it [here](https://1drv.ms/p/s!Am55-JT03hT6gbJad41XiNAYHRYfaw?e=P9Pc6A)), but due to general interest it will be developed further.

Take into account that the idea is to create a core subsystem, but its current form as a local plugin is just a temporary solution for people to be able to understand the different mechanisms that core Moodle components provide and to use it in a local setting without modifying core code.

However, its current status is far from being production ready, so please do not use it in production environments. Take a look at the [To do](#to-do) section for more information.

## Concept

The idea behind the subsystem is to provide a simple gamification framework that may be implemented and extended by other developers to create their own gamification features. Since it needed to be as generic as possible, it was developed around what it was considered the most important element of gaming in general: points. There's no levels, leaderboards or any other typical gamification elements included, since those features are expected to be implemented as subplugins or as totally different types of plugins.

## Installation

1. Clone this repository into the `local/game` directory of your Moodle installation.
2. Visit your site as an admin and follow the on-screen instructions.

## Configuration

The gamification subsystem is configured via the Moodle administration interface.

The current settings include:

- Enabling or disabling the gamification features site-wide.
- Enabling or disabling the global gamification instance.

## Usage

The gamification system allows users to earn points in four different manners:

- By carrying out certain actions (events)
- By completing activities or courses (completion)
- By receiving them from other users (manual giveaway)
- By earning them in external systems (external)

In order for users to be able to earn points, there needs to be an active gamification instance in the context where the conditions for earning the points are met. This instance can be enabled in three differente contexts:

- CONTEXT_SYSTEM
- CONTEXT_COURSECAT
- CONTEXT_COURSE

There's a inheritance mechanism between contexts, so if a gamification instance is enabled in a parent context, it will automatically be enabled in all its children contexts. However, there's also a bottom-up hierarchy, meaning that a children context can override the settings of its parent context and allow users with the `local/game:manage` capabilities to choose between three options:

- Inherit the gamification instance from the parent context
- Create a new gamification instance in the current context
- Disable gamification for the current context

Inside a gamification instance, users with the `local/game:manage` capabilities can configure the following local settings:

- Scope of the instance: inherited, local or disabled
- List of events that will trigger the awarding of points: event, points given an maximum frequency per time interval (for instance, the `\mod_forum\event\post_created` event may award 10 points for a maximum of 10 times per day)
- Default points awarded for completing activities or courses (it can be overridden in the activity or course settings)
- Default points awarded for manual giveaways (it can be overridden when giving away points to a user)
- Maximum points awarded in external systems

## To do

- The logic of how the system is implemented by other plugins needs to be revisited, since the current form forces developers to create the Game() class if it's not available in the global scope.
- The data model needs to be revisited as well.
- The `course_module_completion_updated` and `course_completion_updated` callbacks in `classes/observers.php` need to be developed.
- Both the Core API and the External API need to be developed.
- The subplugin core component needs to be implemented.
- Testing. Lots of testing. Unit tests, behat tests, etc.
- There are no front-end developments yet, except for the setting pages and other elements included in the UI by Moodle core components. The pending front-end developments include, but probably are not limited to:
    - The local settings page for the gamification instance (`instance.php`) needs to be finished. This includes the multiform defined in `classes/instance_form.php`.
    - The course and activity settings needs to be extended in order to include the gamification settings.
    - The user profile page needs to be extended in order to include the gamification features (awarding points, showing the points history, etc.).

## Issues

- There may be an error in the implementation of the `local_game_extend_settings_navigation` hook, since the gamification settings are not shown in the expected position.
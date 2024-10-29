# IfCastle package installer

## Installation

To install the package, run the following command:

```bash
composer require ifcastle/package-installer
```

## Usage

The package uses the description from `composer.json` to configure 
the application's `BootloaderManager`. 
The package **adds**, **updates**, or **removes** a package from the `Bootloader` zone.

To use the package, add the following configuration to the `composer.json` file:

```json
{
  "extra": {
    "ifcastle-installer": {
      "package": {
        "name": "configurator",
        "bootloaders": [
          "IfCastle\\Configurator\\ConfigApplication"
        ],
        "applications": [
          "console",
          "web"
        ]
      }
    }
  }
}
```

or 

```json
{
  "extra": {
    "ifcastle-installer": {
      "package": {
        "name": "configurator",
        "groups": [
          {
            "isActive": true,
            "bootloaders": ["list of classes"],
            "applications": ["application1", "application2"],
            "runtimeTags": ["tag1", "tag2"],
            "excludeTags": ["tag3", "tag4"],
            "group": "configurator"
          }
        ]
      }
    }
  }
}
```

* `ifcastle-installer` - Name of an installer section.
* `package` - Main package section.
* `name` - Name of the package.
* `bootloaders` - List of bootloader classes which will be added to the `Bootloader` zone.
* `applications` - A list of strings, tags that indicate the type of application 
for which the specified `Bootloader` will be applied.
* `groups` - A list of groups that contain the following fields:
  * `isActive` - A boolean value that indicates whether the group is active.
  * `bootloaders` - List of bootloader classes which will be added to the `Bootloader` zone.
  * `applications` - A list of strings, tags that indicate the type of application 
  for which the specified `Bootloader` will be applied.
  * `runtimeTags` - A list of tags that must be defined at the application's startup for the Bootloader 
  to include the specified classes in the loading stage.
  * `excludeTags` - A list of tags that must not be defined at the application's startup for the Bootloader
  * `group` - A string that indicates the group name.
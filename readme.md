# Installation

Add the repository to your `composer.json` file as shown below:

```json
{
  "repositories": [
    {
      "type": "package",
      "package": {
        "name": "yoerioptr/drupal_utilities",
        "version": "dev-main",
        "type": "drupal-module",
        "source": {
          "url": "https://github.com/yoerioptr/drupal_utilities.git",
          "type": "git",
          "reference": "main"
        }
      }
    }
  ]
}
```

Install the module using composer:

```bash
composer require yoerioptr/drupal_utilities:@dev
```
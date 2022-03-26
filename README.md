# bga-innovation

### Development flow:

In order to test changes to the game, you need to be able to copy the code out of our GitHub repository and into a personal BGA project and then back again.

#### Prerequisites
- Create a BGA developer account on https://studio.boardgamearena.com/.
- Create your own BGA Studio project for Innovation at https://studio.boardgamearena.com/studio. Make sure to suffix the new project name with your username (e.g. `InnovationJohnSmith`).
- Optionally Install github desktop (https://desktop.github.com/) -> this is a simpler way of using git if you want to skip the commandline
- Clone this repository onto your computer (`git clone https://github.com/micahstairs/bga-innovation.git innovation`). You'll make your life easier if the directory is named `innovation` instead of `bga-innovation`.
- Clone https://github.com/elaskavaia/bga-sharedcode onto your computer (`git clone https://github.com/elaskavaia/bga-sharedcode.git`).
- Install PHP (https://windows.php.net/download#php-7.3).
- Install SASS (https://sass-lang.com/install) - only needed if you are going to make changes to the SCSS
- Install VSCode (https://code.visualstudio.com/download)

#### Setting up your branch
In order to submit changes to this repository you will need to eventually submit a pull request with your changes. This means you need to have your own local branch. Before running the following commands, make sure you are on the branch of this repository that you want to diverge from (e.g. `main-dev`) and that you have called `git pull` so that you have the latest changes.
```
git branch johnsmith
git checkout johnsmith
```

#### Copying to your project
Use the `bgaprojectrename.php` script from the https://github.com/elaskavaia/bga-sharedcode repository you cloned. For example, if your BGA project is named `InnovationJohnSmith`, you would call a command like:
```
php bga-sharedcode/misc/bgaprojectrename.php innovation innovationjohnsmith 
```
**WARNING:** In order for the script to work correctly, the directory containing our repository should be named `innovation` not `bga-innovation`.

After using the `bgaprojectrename.php` script, check the `innovationjohnsmith.action.php` file and make sure the file contains `class action_innovationjohnsmith extends APP_GameAction` instead of `class action_innovationjohnsmithjohnsmith extends APP_GameAction`. If the name of the action class is wrong, then when you start a game you will just see a blank white screen.

#### Make your changes
Make all of your changes in this other directory (e.g. `innovationjohnsmith`), testing them on https://studio.boardgamearena.com/ by pushing them to your BGA project. This can be accomplished by using the SFTP extension in VSCode.

#### Copying from your project
After you've tested your changes, copy the changes back to your local branch in our repository.
```
php bga-sharedcode/misc/bgaprojectrename.php innovationjohnsmith innovation  
```

#### Creating pull request
Push your local branch into the GitHub repository.
```
git push -u origin johnsmith
```
Then navigate to https://github.com/micahstairs/bga-innovation/pulls, click "New pull request", and follow the prompts there.

#### Setting up VSCode
- Install the SFTP extension (https://marketplace.visualstudio.com/items?itemName=liximomo.sftp)
- Set up your `sftp.json` by using the command shortuct `Ctrl+Shift+P` and runing `SFTP: config`. Paste the following, replacing `johnsmith` and `password` as appropriate.
```
{
    "name": "BGA",
    "host": "1.studio.boardgamearena.com",
    "protocol": "sftp",
    "port": 22,
    "username": "johnsmith",
    "password": "password",
    "remotePath": "/innovationjohnsmith/",
    "uploadOnSave": true,
    "ignore": [
        ".vscode",
        ".git",
        ".DS_Store",
        "misc/",
        "node_modules",
        "package-lock.json"
    ],
    "syncOption": {
        "skipCreate": false,
        "delete": true 
    }
}
```
- Sometimes you will have to run a manual push of your files and you can do that by using the command shortcut `Ctrl+Shift+P` and runing `SFTP: Sync Local -> Remote`
- If you are working with SCSS you should perform these steps
    - Install Run on Save extension (https://marketplace.visualstudio.com/items?itemName=pucelle.run-on-save)
    - Configure the Run on Save extention by clicking the Extensions tab in VSCode -> Gear icon on Run on Save -> Extension Settings -> Edit settings.json -> copy this into that file
    ```
    "runOnSave.commands": [
            {
                "match": ".*\\.scss$",
                "command": "sass ${file} ${fileDirname}/${fileBasenameNoExtension}.css",
                "runIn": "backend",
                "runningStatusMessage": "Compiling ${fileBasename}",
                "finishStatusMessage": "${fileBasename} compiled"
            }
        ]
    ```
 
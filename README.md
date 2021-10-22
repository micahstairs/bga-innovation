# bga-innovation

### Development flow:

In order to test changes to the game, you need to be able to copy the code out of our GitHub repository and into a personal BGA project and then back again.

#### Prerequisites
- Create a BGA developer account on https://studio.boardgamearena.com/.
- Create your own BGA Studio project for Innovation at https://studio.boardgamearena.com/studio. Make sure to suffix the new project name with your username (e.g. `InnovationJohnSmith`).
- Clone this repository onto your computer (`git clone https://github.com/micahstairs/bga-innovation.git innovation`). You'll make your life easier if the directory is named `innovation` instead of `bga-innovation`.
- Clone https://github.com/elaskavaia/bga-sharedcode onto your computer (`git clone https://github.com/elaskavaia/bga-sharedcode.git`).

#### Setting up your branch
In order to submit changes to this repository you will need to eventually submit a pull request with your changes. This means you need to have your own local branch. Before running the following commands, make sure you are on the branch of this repository that you want to diverge from (e.g. `main`) and that you have called `git pull` so that you have the latest changes.
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

#### Make your changes
Make all of your changes in this other directory (e.g. `innovationjohnsmith`), testing them on https://studio.boardgamearena.com/ by pushing them to your BGA project. This can be accomplished by using the SFTP extension in VSCode, for example.

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
Then navigate to https://github.com/elaskavaia/bga-sharedcode/pulls, click "New pull request", and follow the prompts there.

### Tips and Tricks

- If you are using the SFTP extension in VSCode to sync code to your BGA project, set up your `sftp.json` file something like the following, replacing `johnsmith` and `password` as appropriate.
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
        ".DS_Store"
    ]
}
```

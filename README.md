cs492 WesternList Project
=====

How to use
Create a repo:
    set up your keys (below)
    git clone git@github.com:Maome/cs492.git

Save a modified file to the repository:
    git commit -am 'update message goes here'
    //the -a stages all modified files automatically
    git push

Add a new file to the repository:
    git add {file}
    git commit -am 'update message about file'
    git push

Aquire the latest working version of the repository:
    git pull

Aw fuck I broke something:
    rm -rf cs492
    git clone git@github.com:Maome/cs492.git

How to setup ssh keys with git (aka how to stop entering your password for ever push)
1. ssh-keygen -t rsa -C "yourgitaccountemail@email.com"
2. entering a password is optional, double tap enter to skip (applying a password will require you to enter the password each time you use this key)
3. go to https://github.com/settings/ssh and click "Add SSH key"
4. copy the public key you just made into github and use any name
    4.1 cat ~/.ssh/id_rsa.pub
    4.2 the format should be "ssh-rsa {reallylongkey} yourgetaccountemail@email.com" with no newlines or extra whitespace
5. remove your repo and use git clone git@github.com:Maome/cs492.git to recreate it
6. Now you can push and pull all day without providing credentials every time!

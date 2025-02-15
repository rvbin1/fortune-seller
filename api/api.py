import requests
import json
import time

# für Items

""" response = requests.get("https://api.guildwars2.com/v2/items/1598")
items = json.loads(response.content)
print(response.content)
all_ids = []
exit()
for id in items:
    item_data = requests.get(f"https://api.guildwars2.com/v2/items/{id}")
    item_data_json = json.loads(item_data.content)
    try:
        item_name = item_data_json["name"]
    except:
        time.sleep(180)
        item_data = requests.get(f"https://api.guildwars2.com/v2/items/{id}")
        print(item_data.content)
        item_data_json = json.loads(item_data.content)
        item_name = item_data_json["name"]

    with open("items.txt", "a+") as file:
        all_ids.append(id)
        print(id, item_name)
        file.write(f"{id}:{item_name}\n")

print(all_ids) """


""" # für Rezepte
response = requests.get("https://api.guildwars2.com/v2/recipes/search?input=19976")
items = json.loads(response.content)
print(items)
for id in items:
    item_data = requests.get(f"https://api.guildwars2.com/v2/recipes/{id}")
    item_data_json = json.loads(item_data.content)
    print(item_data_json) """


#Rezept ids rausbekommen mit Item namen welche als Output existiert.

all_ids = {}
with open("items.txt") as file:
    for line in file.readlines():
        id = int(line.split(":")[0])
        all_ids[id] = line.split(":")[1].strip("\n")

response = requests.get("https://api.guildwars2.com/v2/recipes/")
recipes = json.loads(response.content)
for recipe_id in recipes:
    print("Zu suchende Id", recipe_id)
    recipe_data = requests.get(f"https://api.guildwars2.com/v2/recipes/{recipe_id}")
    recipe_data_json = json.loads(recipe_data.content)
    try:
        output_item_id = recipe_data_json["output_item_id"]
    except:
        print("Wartezeit")
        time.sleep(180)
        recipe_data = requests.get(f"https://api.guildwars2.com/v2/recipes/{recipe_id}")
        print(recipe_data.content)
        recipe_data_json = json.loads(recipe_data.content)
        output_item_id = recipe_data_json["output_item_id"]

    with open("recipes.txt", "a+") as file:
        file.write(f"{recipe_id}:{output_item_id}:{all_ids[output_item_id]}\n")

""" 
Also unterschiedliche IDs für recipes und items
heißt zwei Anfragen welche rezepte, welche items da rauskommen, und dann das item abfragen
In der Datenbank sollte es also im besten Fall die Eigenschaften geben: Name, Item_id, picutre_id, verkäuflich, mystic forge (man muss da an eine andere Seite ein Anfrage senden),rezept_id (damit man weiß welches Rezept welches item hat und nicht erst mit einem api zugriff nachgeguckt werden muss.)
 """
#TODO noch mal alle Items eingeben da manche Items welche Rezept im Namen tragen nicht richtig gelesen werden konnten.
#TODO Ausgabe soll in JSON Format passieren und sonst mit Flags soll start des Programmes gemacht werden. 
#TODO 

item -> rezepten_ids -> item -> verkäuft
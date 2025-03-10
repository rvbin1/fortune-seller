import requests
import json
from collections import defaultdict

PREFIXES = ["Berserker's", "Zealot's", "Soldier's", "Forsaken", "Valkyrie", "Harrier's", "Heretic", "Tyrant", "Paladin", "Commander's", "Demolisher", "Swashbuckler", "Marauder", "Avatar", "Destroyer", "Vigilant", "Crusader", "Wanderer's", "Diviner's", "Dragon's", "Wizard", "Viper's", "Grieving", "Sage", "Marshal's", "Captain's", "Rampager's", "Assassin's", "Seraph", "Knight's", "Cavalier's", "Nomad's", "Settler's", "Giver's", "Trailblazer's", "Minstrel's", "Sentinel's", "Shaman's", "Plaguedoctor's", "Ritualist's", "Sinister", "Carrion", "Rabid", "Dire", "Apostate's", "Bringer's", "Cleric's", "Magi's", "Apothecary's", "Celestial", "Mighty", "Strong", "Vagabond", "Vigorous", "Potent", "Honed", "Precise", "Hunter", "Penetrating", "Resilient", "Stout", "Vital", "Hearty", "Mystical", "Malign", "Ravaging", "Deserter", "Lingering", "Spiteful", "Healing", "Rejuvenating", "Survivor", "Mending"]

SUFFIXES = ["Might", "Strength", "Vagabond", "Vigor", "Potency", "Honing", "Precision", "Hunter", "Penetration", "Resilience", "Stout", "Vitality", "Heartiness", "Luck", "Festering", "Ravaging", "Deserter", "Lingering", "Spiteful", "Compassion", "Rejuvenating", "Survivor", "Mending", "the Pack", "Hoelbrak", "the Dolyak", "Lyssa", "the Flame Legion", "Melandru", "Scavenging", "the Flock", "Mercy", "Rage", "the Afflicted", "Grenth", "the Eagle", "Dwayna", "the Centaur", "Divinity", "Vampirism", "Infiltration", "of Fire"]

def get_item_data(url: str) -> dict:
    """
    This function makes an api request for one or multiple items and returns the wanted attributes about name, id, icon and item_attributes

    :param url: The request to be send
    :type url: str
    :return: returnes a dict with all ids and attributes
    :rtype: dict
    """
    response = requests.get(url)
    return_json = json.loads(response.content)
    if isinstance(return_json, dict):
        # default dict so that missing icon or attributes in an item don't break the code
        return_json = defaultdict(str, return_json)
        # for attribute 'details' a new dict has to be made because defaultdict can only check the first level key
        if type(return_json["details"]) != str:
            if "infix_upgrade" in return_json["details"]:
                item_data_details = defaultdict(list, return_json["details"]["infix_upgrade"])
            else: 
                item_data_details = defaultdict(list)
        else: 
            item_data_details = defaultdict(list)
        return {return_json["id"]: {
            "name": return_json["name"],
            "icon": return_json["icon"],
            "attributes": item_data_details["attributes"],
        }}
    else:
        return_dict = {}
        for item_data in return_json:
            item_data = defaultdict(str, item_data)
            # for details a new dict has to be made because defaultdict can only check the first level key and has to be checked if its there
            if type(item_data["details"]) != str:
                if "infix_upgrade" in item_data["details"]:
                    item_data_details = defaultdict(list, item_data["details"]["infix_upgrade"])
                else: 
                    item_data_details = defaultdict(list)
            else: 
                item_data_details = defaultdict(list)
            return_dict[item_data["id"]] = {
                "name": item_data["name"],
                "icon": item_data["icon"],
                "attributes": item_data_details["attributes"],
            }
        return return_dict

def get_sellable_data(url: str) -> dict:
    """
    This function makes an api request for one or multiple item sell prices and returns if the item/s are sellable.

    :param url: The request to be send
    :type url: str
    :return: returnes a dict with the ids and sell status
    :rtype: dict
    """
    response = requests.get(url)
    return_json = json.loads(response.content)
    if isinstance(return_json, dict):
        # if all ids have no sell value an empty dict needs to be returned
        if "all ids provided are invalid" in response.text:
            return {}
        # here it needs a try-statement has to be put in since a defaultdict would still give a value back
        try:
            return {return_json["id"]: True}
        except:
            return {return_json["id"]: False}
    else:
        return_dict = {}
        for item_data in return_json:
            return_dict[item_data["id"]] = True
        return return_dict
    
def get_recipe_data(url: str) -> dict:
    """
    This function makes an api request for one or multiple recipes and returns the wanted attributes about id, output_item, ingredients

    :param url: The request to be send
    :type url: str
    :return: returnes a dict with all ids and attributes
    :rtype: dict
    """
    response = requests.get(url)
    return_json = json.loads(response.content)
    if isinstance(return_json, dict):
        return {return_json["id"]: {
            "output_item_id": return_json["output_item_id"],
            "output_item_count": return_json["output_item_count"],
            "ingredients": return_json["ingredients"],
        }}
    else:
        return_dict = {}
        for item_data in return_json:
            item_data = defaultdict(bool, item_data)
            return_dict[item_data["id"]] = {
                "output_item_id": item_data["output_item_id"],
                "output_item_count": item_data["output_item_count"],
                "ingredients": item_data["ingredients"],
            }
        return return_dict

def write_item_data() -> list:
    """
    This function counts item ids to build an url and collects via a request call all information about the items available

    :return: returnes a dict with all item and the attributes: id, name, pic_url, sellable, item_attributes
    :rtype: dict
    """
    item_content = []
    response = requests.get("https://api.guildwars2.com/v2/items/")
    item_ids = json.loads(response.content)
    i = 0
    item_url = "https://api.guildwars2.com/v2/items?ids="
    sell_url = "https://api.guildwars2.com/v2/commerce/prices?ids="
    for id in item_ids:
        if i < 200:
            i += 1
            item_url = item_url + str(id) + ","
            sell_url = sell_url + str(id) + ","
        if i == 199:
            item_url = item_url.removesuffix(",")
            sell_url = sell_url.removesuffix(",")
            item_data = get_item_data(item_url)
            sell_data = defaultdict(bool, get_sellable_data(sell_url))
            for item in item_data:
                item_name = item_data[item]["name"].replace(" ", "_")
                if item_name.split("_")[0] in PREFIXES:
                    item_name = item_name.replace(item_name.split("_")[0], "")
                try:
                    if item_name.split("of_")[1] in SUFFIXES:
                        item_name = item_name.split("_of_")[0]
                except:
                    pass
                wiki_url = f"https://wiki.guildwars2.com/wiki/{item_name}"
                item_content.append({"gw2id": item, "name": item_data[item]["name"], "pic_url": item_data[item]["icon"], "sellable": sell_data[item], "attributes": item_data[item]["attributes"], "wiki_url": wiki_url})
            
            # back to initial state
            i = 0
            item_url = "https://api.guildwars2.com/v2/items?ids="
            sell_url = "https://api.guildwars2.com/v2/commerce/prices?ids="
            
        # because the amount of ids are not dividable by 200
        if id == item_ids[-1]:
            item_url = item_url.removesuffix(",")
            sell_url = sell_url.removesuffix(",")
            item_data = get_item_data(item_url)
            sell_data = defaultdict(bool, get_sellable_data(sell_url))
            for item in item_data:
                item_name = item_data[item]["name"].replace(" ", "_")
                if item_name.split("_")[0] in PREFIXES:
                    item_name = item_name.replace(item_name.split("_")[0], "")
                try:
                    if item_name.split("of_")[1] in SUFFIXES:
                        item_name = item_name.split("_of_")[0]
                except:
                    pass
                wiki_url = f"https://wiki.guildwars2.com/wiki/{item_name}"
                item_content.append({"gw2id": item, "name": item_data[item]["name"], "pic_url": item_data[item]["icon"], "sellable": sell_data[item], "attributes": item_data[item]["attributes"], "wiki_url": wiki_url})
            

    return item_content

def write_recipe_data() -> list:
    """
    This function counts recipe ids to build an url and collects via a request call all information about the items available

    :return: returnes a dict with all item and the attributes: id, output_item_id, ingredients
    :rtype: dict
    """
    recipe_content = []
    response = requests.get("https://api.guildwars2.com/v2/recipes/")
    recipes_ids = json.loads(response.content)
    i = 0
    recipe_url = "https://api.guildwars2.com/v2/recipes?ids="
    for id in recipes_ids:
        if i < 200:
            i += 1
            recipe_url = recipe_url + str(id) + ","
        if i == 199:
            recipe_url = recipe_url.removesuffix(",")
            item_data = get_recipe_data(recipe_url)
            for item in item_data:
                recipe_content.append({"gw2_id": item, "output_item_id": item_data[item]["output_item_id"], "output_item_count": str(item_data[item]["output_item_count"]), "ingredients": item_data[item]["ingredients"]})

            # back to initial state
            i = 0
            recipe_url = "https://api.guildwars2.com/v2/recipes?ids="
        if id == recipes_ids[-1]:
            recipe_url = recipe_url.removesuffix(",")
            item_data = get_recipe_data(recipe_url)
            for item in item_data:
                recipe_content.append({"gw2_id": item, "output_item_id": item_data[item]["output_item_id"], "output_item_count": str(item_data[item]["output_item_count"]), "ingredients": item_data[item]["ingredients"]})

    return recipe_content

def write_mystic_forge_data() -> list:
    """
    This function makes an api call for the mystic forge recipes and collect data of all available recipes

    :return: returnes a dict with all item and the attributes: id, output_item_id, ingredients
    :rtype: dict
    """

    # because a different api need to be used for the mystic forge the code is shorter
    recipe_content = []
    response = requests.get("https://gw2profits.com/json/v3?disciplines=Mystic%20Forge")
    mystic_forge_recipes = json.loads(response.content)
    for mystic_forge_recipe in mystic_forge_recipes:
        if "output_item_count_range" in mystic_forge_recipe:
            recipe_content.append({"gw2_id": mystic_forge_recipe["id"], "output_item_id": mystic_forge_recipe["output_item_id"], "output_item_count": str(mystic_forge_recipe["output_item_count_range"]), "ingredients": mystic_forge_recipe["ingredients"]})
        else:
            recipe_content.append({"gw2_id": mystic_forge_recipe["id"], "output_item_id": mystic_forge_recipe["output_item_id"], "output_item_count": str(mystic_forge_recipe["output_item_count"]), "ingredients": mystic_forge_recipe["ingredients"]})

    return recipe_content

def main():
    print("Collecting item data")
    item_content = write_item_data()
    print("Finished collecting item data. Writing item.json file")
    with open('./webapp/public/json/items.json', 'w', encoding='utf-8') as f:
        json.dump(item_content, f, ensure_ascii=False, indent=4)  

    print("Collecting recipe data")
    recipes_content = write_recipe_data()
    print("Finished collecting recipe data. Writing recipe.json file")
    with open("./webapp/public/json/recipe.json", "w", encoding="utf-8") as f:
        json.dump(recipes_content, f, ensure_ascii=False, indent=4) 

    print("Collecting Mystic forge recipe data")
    mystic_content = write_mystic_forge_data()
    print("Finished collecting mystic forge recipe data. Writing mysticForge.json file")
    with open("./webapp/public/json/mysticForge.json", "w", encoding="utf-8") as f:
        json.dump(mystic_content, f, ensure_ascii=False, indent=4)

if __name__ == "__main__": 
    main()

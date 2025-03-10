import pytest 
import api
"""
get_item_data
get_sellable_data
get_recipe_data
write_item_data
write_recipe_data
write_mystic_forge_data
"""

#test function get_item_data
def test_get_item_data_wrong_url():
    with pytest.raises(Exception) as e_info:
        api.get_item_data("wrongurl")

def test_get_item_data_passes_one_id():
    assert api.get_item_data("https://api.guildwars2.com/v2/items/56") == {56: {"name": "Strong Back Brace", "icon": "https://render.guildwars2.com/file/03B65C435B15EB2C10E04F3454B03718AAF3AE90/61004.png", "attributes": [{"attribute": "Power", "modifier": 5}, {"attribute": "Precision", "modifier": 3}]}}

def test_get_item_data_passes_one_wrong_id():
    assert api.get_item_data("https://api.guildwars2.com/v2/items/0") == {'': {'attributes': [], 'icon': '', 'name': ''}}

def test_get_item_data_passes_multiple_ids():
    assert api.get_item_data("https://api.guildwars2.com/v2/items?ids=56,57") == {56: {"name": "Strong Back Brace", "icon": "https://render.guildwars2.com/file/03B65C435B15EB2C10E04F3454B03718AAF3AE90/61004.png", "attributes": [{"attribute": "Power", "modifier": 5}, {"attribute": "Precision", "modifier": 3}]},
                                                                              57: {"name": "Hearty Back Brace", "icon": "https://render.guildwars2.com/file/03B65C435B15EB2C10E04F3454B03718AAF3AE90/61004.png", "attributes": [{"attribute": "Toughness", "modifier": 3}, {"attribute": "Vitality", "modifier": 5}]}}
def test_get_item_data_passes_with_one_wrong_ids():
    assert api.get_item_data("https://api.guildwars2.com/v2/items?ids=0,56") == {56: {'attributes': [{'attribute': 'Power', 'modifier': 5}, {'attribute': 'Precision', 'modifier': 3}], 'icon': 'https://render.guildwars2.com/file/03B65C435B15EB2C10E04F3454B03718AAF3AE90/61004.png', 'name': 'Strong Back Brace'}}

def test_get_item_data_passes_with_onyl_wrong_ids():
    assert api.get_item_data("https://api.guildwars2.com/v2/items?ids=0,102938957") == {'': {'attributes': [], 'icon': '', 'name': ''}}


#test function get_sellable_data
def test_get_sellable_data_wrong_url():
    with pytest.raises(Exception) as e_info:
        api.get_sellable_data("wrongurl")

def test_get_sellable_data_passes_one_id():
    assert api.get_sellable_data("https://api.guildwars2.com/v2/commerce/prices/68") == {68: True}

def test_get_sellable_data_passes_one_wrong_id():
    with pytest.raises(Exception) as e_info:
        api.get_sellable_data("https://api.guildwars2.com/v2/commerce/prices/0")

def test_get_sellable_data_passes_multiple_ids():
    assert api.get_sellable_data("https://api.guildwars2.com/v2/commerce/prices?ids=68,69") == {68: True, 69: True}

def test_get_sellable_data_passes_with_one_wrong_ids():
    assert api.get_sellable_data("https://api.guildwars2.com/v2/commerce/prices?ids=0,68") == {68: True}

def test_get_sellable_data_with_only_wrong_ids():
    assert api.get_sellable_data("https://api.guildwars2.com/v2/commerce/prices?ids=0,102938957") == {}

#test function get_recipe_data
def test_get_recipe_data_wrong_url():
    with pytest.raises(Exception) as e_info:
        api.get_recipe_data("wrongurl")

def test_get_recipe_data_passes_one_id():
    assert api.get_recipe_data("https://api.guildwars2.com/v2/recipes/1") == {1: {"output_item_id": 19713, "ingredients": [{"item_id": 19726,"count": 2}]}}

def test_get_recipe_data_passes_one_wrong_id():
    with pytest.raises(Exception) as e_info:
        api.get_recipe_data("https://api.guildwars2.com/v2/recipes/0")

def test_get_recipe_data_passes_multiple_ids():
    assert api.get_recipe_data("https://api.guildwars2.com/v2/recipes?ids=1,2") == {1: {"output_item_id": 19713, "ingredients": [{"item_id": 19726,"count": 2}]}, 2: {"output_item_id": 19712, "ingredients": [{"item_id": 19725,"count": 3}]}}

def test_get_recipe_data_passes_with_one_wrong_ids():
    assert api.get_recipe_data("https://api.guildwars2.com/v2/recipes?ids=0,1") == {1: {"output_item_id": 19713, "ingredients": [{"item_id": 19726,"count": 2}]}}

def test_get_recipe_data_with_only_wrong_ids():
    with pytest.raises(Exception) as e_info:
        api.get_recipe_data("https://api.guildwars2.com/v2/recipes?ids=0,102938957")

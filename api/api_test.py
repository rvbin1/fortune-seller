from unittest.mock import patch
import unittest
import api
import requests

class TestGetItemData(unittest.TestCase):
    def test_get_item_data_wrong_url(self) -> None:
        with self.assertRaises(requests.exceptions.ConnectionError) as e_info:
            api.get_item_data("https://wrongurl")

    @patch('requests.get')
    def test_get_item_data_passes_one_id_without_details(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = {"id": 56, "name": "TestName", "icon": "TestIcon"} 
        url = 'https://mock.com/api/item/1'
        result = api.get_item_data(url)  
        
        self.assertEqual(result, {56: {"name": "TestName", "icon": "TestIcon", "attributes":  []}})  
        mock_get.assert_called_once_with(url)  
        
    @patch('requests.get')
    def test_get_item_data_passes_one_id_with_details_without_infix(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = {"id": 56, "name": "TestName", "icon": "TestIcon", "details": {}} 
        url = 'https://mock.com/api/item/1'
        result = api.get_item_data(url)  
        
        self.assertEqual(result, {56: {"name": "TestName", "icon": "TestIcon", "attributes":  []}})  
        mock_get.assert_called_once_with(url)  

    @patch('requests.get')
    def test_get_item_data_passes_one_id_with_details_with_infix(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = {"id": 56, "name": "TestName", "icon": "TestIcon", "details": {"infix_upgrade": {"attributes": [{"attribute": "Power","modifier": 5},{"attribute": "Precision","modifier": 3}]}}} 
        url = 'http://mock.com/api/item/1'
        result = api.get_item_data(url)  
        
        self.assertEqual(result, {56: {"name": "TestName", "icon": "TestIcon", "attributes":  [{"attribute": "Power","modifier": 5},{"attribute": "Precision","modifier": 3}]}})  
        mock_get.assert_called_once_with(url)

    @patch('requests.get')
    def test_get_item_data_wrong_id(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = {"text": "no such id"} 
        url = 'http://mock.com/api/item/0'
        result = api.get_item_data(url)  
        
        self.assertEqual(result, {'': {'attributes': [], 'icon': '', 'name': ''}})
        mock_get.assert_called_once_with(url)
    
    @patch('requests.get')
    def test_get_item_data_passes_multiple_ids_without_details(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = [{"id": 56, "name": "TestName", "icon": "TestIcon"}, {"id": 57, "name": "TestName", "icon": "TestIcon"}]
        url = 'https://mock.com/api/item?=ids1,2'
        result = api.get_item_data(url)   
                
        self.assertEqual(result, {56: {'name': 'TestName', 'icon': 'TestIcon', 'attributes': []}, 57: {'name': 'TestName', 'icon': 'TestIcon', 'attributes': []}})
        mock_get.assert_called_once_with(url)  

    @patch('requests.get')
    def test_get_item_data_passes_multiple_ids_with_details_without_infix(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = [{"id": 56, "name": "TestName", "icon": "TestIcon"}, {"id": 57, "name": "TestName", "icon": "TestIcon", "details": {}}]
        url = 'https://mock.com/api/item?=ids1,2'
        result = api.get_item_data(url)   
                
        self.assertEqual(result, {56: {'name': 'TestName', 'icon': 'TestIcon', 'attributes': []}, 57: {'name': 'TestName', 'icon': 'TestIcon', 'attributes': []}})
        mock_get.assert_called_once_with(url)

    @patch('requests.get')
    def test_get_item_data_passes_multiple_ids_with_details_with_infix(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = [{"id": 56, "name": "TestName", "icon": "TestIcon"}, {"id": 57, "name": "TestName", "icon": "TestIcon", "details": {"infix_upgrade": {"attributes": [{"attribute": "Power","modifier": 5},{"attribute": "Precision","modifier": 3}]}}}]
        url = 'https://mock.com/api/item?=ids1,2'
        result = api.get_item_data(url)   
                
        self.assertEqual(result, {56: {'name': 'TestName', 'icon': 'TestIcon', 'attributes': []}, 57: {'name': 'TestName', 'icon': 'TestIcon', 'attributes': [{"attribute": "Power","modifier": 5},{"attribute": "Precision","modifier": 3}]}})
        mock_get.assert_called_once_with(url)

    @patch('requests.get')
    def test_get_item_data_wrong_ids(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = {"text": "all ids provided are invalid"} 
        url = 'https://mock.com/api/item?=ids1,2'
        result = api.get_item_data(url)  
        
        self.assertEqual(result, {'': {'attributes': [], 'icon': '', 'name': ''}})
        mock_get.assert_called_once_with(url)    


class TestGetSellableData(unittest.TestCase):

    def test_get_recipe_data_wrong_url(self) -> None:
        with self.assertRaises(requests.exceptions.ConnectionError) as e_info:
            api.get_item_data("https://wrongurl")

    @patch('requests.get')
    def test_get_item_data_passes_one_id_with_price(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = {"id": 68,"buys": {"quantity": 1,"unit_price": 75}}
        url = 'https://mock.com/api/price/1'
        result = api.get_sellable_data(url)  

        self.assertEqual(result, {68: True})  
        mock_get.assert_called_once_with(url) 

    @patch('requests.get')
    def test_get_item_data_passes_multiple_ids(self, mock_get: patch):
        mock_response = mock_get.return_value  
        mock_response.json.return_value = [{"id": 68,"buys": {"quantity": 1,"unit_price": 75}}, {"id": 69,"buys": {"quantity": 1,"unit_price": 57}}]
        url = 'https://mock.com/api/price/0'
        result = api.get_sellable_data(url)  

        self.assertEqual(result, {68: True, 69: True})  
        mock_get.assert_called_once_with(url) 

if __name__ == '__main__':
    unittest.main()
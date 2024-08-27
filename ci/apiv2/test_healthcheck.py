from hashtopolis import HealthCheck
from utils import BaseTest


class HealthCheckAgentTest(BaseTest):
    model_class = HealthCheck

    def create_test_object(self, *nargs, **kwargs):
        return self.create_healthcheck(*nargs, **kwargs)

    def test_create(self):
        model_obj = self.create_test_object()
        self._test_create(model_obj)

    def test_patch(self):
        model_obj = self.create_test_object()
        self._test_patch(model_obj, 'crackerBinaryId', 1)

    def test_delete(self):
        model_obj = self.create_test_object(delete=False)
        self._test_delete(model_obj)

    def test_expandables(self):
        model_obj = self.create_test_object()
        expandables = ['crackerBinary', 'healthCheckAgents']
        self._test_expandables(model_obj, expandables)

<?php

if (!defined("MEDIAWIKI")) {
    echo("This file is an extension to the MediaWiki software and cannot be used standalone.\n");
    die(1);
}

define("JIKI_VIEW_HTML_GREY","background-color:#4a6785;border-color:#4a6785;color:#FFFFFF;border-radius:3px;padding:1px 3px;");
define("JIKI_VIEW_HTML_ORANGE","background-color:#ffd351;border-color:#ffd351;color:#594300;border-radius:3px;padding:1px 3px;");
define("JIKI_VIEW_HTML_GREEN","background-color:#14892c;border-color:#14892c;color:#FFFFFF;border-radius:3px;padding:1px 3px;");

/**
 * HTML renderer for JIRA data that groups issues by component
 */
class HTMLComponent
{
    /**
     * Sorts JIRA response data and groups issues by component. An issue will be stored multiple
     * times if it is associated with more than one component.
     *
     * Shouldn't be in the view class, but meh.
     *
     * @param $response
     * @return array
     */
    public function sort($response)
    {
      $data = array();
      $data['jql'] = $response['jql'];
      $data['host'] = $response['host'];
      $data['endpoint'] = $response['endpoint'];
      $data['total'] = $response['total'];

      $sorted = array();
      foreach ($response['data'] as $issue) {
          if (empty($issue['fields']['components'])) {
              $issue['fields']['components'][0]['name'] = 'Uncategorised';
          }
          foreach ($issue['fields']['components'] as $component) {
              $sorted[$component['name']][] = array(
                  'expand' => $issue['expand'],
                  'id' => $issue['id'],
                  'self' => $issue['self'],
                  'key' => $issue['key'],
                  'fields' => $issue['fields'],
                  'renderedFields' => $issue['renderedFields']
              );
          }
      }
      ksort($sorted);
      $data['data'] = $sorted;
      return $data;
    }


    /**
     * Get the rendered view in HTML format, grouping issues by component and only showing links
     * to those logged in to mediawiki.
     *
     * @param array $data the array of data to be rendered
     * @param array $args
     * @return string
     */
    public function getRenderedView(&$data,$args=array())
    {
        // need this mediawiki global to check if user is logged in
        global $wgUser;

        $renderedView = "";
        if (!empty($data['data'])) {
            foreach($data["data"] as $component => $issues) {
                $renderedView .= "<h3> " . $component . " </h3>";

                foreach ($issues as $issue) {
                    $renderedView .= "<div style=\"margin:10px 0\">";
                    $renderedView .= "<img title=\"" . $issue["fields"]["issuetype"]["name"] . ": " . $issue["fields"]["issuetype"]["description"]
                                   . "\" src=\"" . $issue["fields"]["issuetype"]["iconUrl"] . "\"/> ";
                    $renderedView .= "<strong>" . $issue["key"] . "</strong> ";

                    if ($wgUser->isLoggedIn()) {
                        $renderedView.= "<a href=\"".JIRA::getIssueURL($data["host"],$issue["key"])."\">{$issue["fields"]["summary"]}</a> ";
                    } else {
                        $renderedView .= "<span style=\"color: #0645ad\">" . $issue["fields"]["summary"] . "</span> ";
                    }

                    // JIRA provides color
                    if (isset($issue["fields"]["status"]["statusCategory"])) {
                        #TODO: identify how to use ids for status names or just configure this
                        switch(strtolower($issue["fields"]["status"]["statusCategory"]["colorName"])) {
                            case "green":
                            $statusStyle = JIKI_VIEW_HTML_GREEN;
                            break;
                          case "yellow":
                            $statusStyle = JIKI_VIEW_HTML_ORANGE;
                            break;
                          default:
                            $statusStyle = JIKI_VIEW_HTML_GREY;
                            break;
                        }
                        $renderedView .= "<span style=\"" . $statusStyle . "\">" . $issue["fields"]["status"]["name"] . "</span> ";
                    // color not found
                    } else {
                        $renderedView .= "(" . $issue["fields"]["status"]["name"] . ") ";
                    }

                    if (isset($issue["fixVersions"])) {
                        foreach ($issue["fixVersions"] as $issueFixVersion) {
                            $renderedView .= $issueFixVersion["name"];

                            if (isset($issueFixVersion["releaseDate"]) && isset($issueFixVersion["released"]) && $issueFixVersion["released"] === true) {
                                $renderedView.= "(" . $issueFixVersion["releaseDate"] . ") ";
                            }
                        }
                    }

                    if (isset($args["renderDetails"]) && $args["renderDetails"] === true) {
                        $renderedView .= "<div style=\"margin-left: 20px;\">" . $issue["renderedFields"][JIKI_FULL_DETAILS_FIELD] . "</div>";
                    }
                    $renderedView .= "</div>";
                }
            }
        } else {
            $renderedView .= "<strong>No issues found</strong><br /><br />";
        }
        if (isset($args["renderLink"]) && $args["renderLink"] === true && $wgUser->isLoggedIn()) {
            $renderedView .= "<a href =\"" . JIRA::getFilterURL($data["host"],$data["jql"]) . "\">View in JIRA</a>";
        }

        return $renderedView;
    }
}

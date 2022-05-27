import org.jsoup.Jsoup;
import org.jsoup.nodes.Document;
import org.jsoup.nodes.Element;
import org.jsoup.select.Elements;

import java.io.BufferedReader;
import java.io.File;
import java.io.FileReader;
import java.io.FileWriter;
import java.util.*;


public class ExtractLinks {
    public static void main(String[] args) throws Exception{
        File dir = new File("/Users/shifanzhou/Downloads/foxnews");
        File mapping = new File("/Users/shifanzhou/Downloads/URLtoHTML_fox_news.csv");

        HashMap<String, String> fileUrlMap = new HashMap<>();
        HashMap<String, String> urlFileMap = new HashMap<>();

        BufferedReader br = new BufferedReader(new FileReader(mapping));
        String line = "";
        while ((line = br.readLine()) != null) {
            String[] tokens  = line.split(",");
            fileUrlMap.put(tokens[0],tokens[1]);
            urlFileMap.put(tokens[1],tokens[0]);
        }

        br.close();

        Set<String> edges = new HashSet<String>();
        for (File file: dir.listFiles()) {
            Document doc = Jsoup.parse(file, "UTF-8", fileUrlMap.get(file.getName()));
            Elements links = doc.select("a[href]");
            for (Element link: links) {
                String url = link.attr("abs:href").trim();
                if (urlFileMap.containsKey(url)){
                    edges.add(file.getName() + " " + urlFileMap.get(url));
                }
            }

        }

        FileWriter fileWriter = new FileWriter("/Users/shifanzhou/Downloads/edges.txt");
        for (String s : edges){
            fileWriter.write(s + "\n");
        }
        fileWriter.close();
    }
}
